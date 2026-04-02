<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Playbook\PlaybookManagementActionService;
use App\Service\Discovery\Playbook\PlaybookManagementSurfaceActionResolver;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PlaybookManagementSurfaceActionResolverTest extends TestCase
{
    public function testItResolvesAuditRegistryAction(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-resolver-audit-' . uniqid('', true);
        $resolver = new PlaybookManagementSurfaceActionResolver(new PlaybookManagementActionService(
            new PlaybookFileDiscoverySourceRecordRepository(
                $projectDir,
                new DiscoverySourceRecordJsonFileDecoder(),
                new DiscoverySourceRecordJsonFileEncoder(),
            ),
        ));

        $result = $resolver->resolve(new Request(query: ['action' => 'audit-registry']));

        self::assertNotNull($result);
        self::assertSame('audit-registry', $result->actionName);

        @rmdir($projectDir);
    }

    public function testItResolvesEnsureSampleRegistryAction(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-resolver-seed-' . uniqid('', true);
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );
        $resolver = new PlaybookManagementSurfaceActionResolver(new PlaybookManagementActionService($repository));

        $result = $resolver->resolve(new Request(query: ['action' => 'ensure-sample-registry']));

        self::assertNotNull($result);
        self::assertSame('ensure-sample-registry', $result->actionName);
        self::assertTrue($result->payload['created']);

        foreach ($repository->listStorageFiles() as $path) {
            @unlink($path);
        }
        @rmdir($repository->getStorageDirectoryPath());
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

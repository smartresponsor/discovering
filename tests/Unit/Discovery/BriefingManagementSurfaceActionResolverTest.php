<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Briefing\BriefingManagementActionService;
use App\Service\Discovery\Briefing\BriefingManagementSurfaceActionResolver;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BriefingManagementSurfaceActionResolverTest extends TestCase
{
    public function testItResolvesAuditRegistryAction(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-resolver-audit-' . uniqid('', true);
        $resolver = new BriefingManagementSurfaceActionResolver(new BriefingManagementActionService(
            new BriefingFileDiscoverySourceRecordRepository(
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
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-resolver-seed-' . uniqid('', true);
        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );
        $resolver = new BriefingManagementSurfaceActionResolver(new BriefingManagementActionService($repository));

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

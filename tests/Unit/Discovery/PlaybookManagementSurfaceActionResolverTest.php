<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Resolver\Playbook\DiscoveryPlaybookManagementSurfaceActionResolver;
use App\Discovering\Service\Playbook\DiscoveryPlaybookManagementActionService;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exercises the playbook management surface action resolver test case for the Discovering component.
 */
final class PlaybookManagementSurfaceActionResolverTest extends DiscoveryTempFilesystemTestCase
{
    public function testItResolvesAuditRegistryAction(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-resolver-audit-');
        $resolver = new DiscoveryPlaybookManagementSurfaceActionResolver(new DiscoveryPlaybookManagementActionService(
            new DiscoveryPlaybookFileSourceRecordRepository(
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
        $projectDir = $this->createTempDirectory('discovering-playbook-resolver-seed-');
        $repository = new DiscoveryPlaybookFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );
        $resolver = new DiscoveryPlaybookManagementSurfaceActionResolver(new DiscoveryPlaybookManagementActionService($repository));

        $result = $resolver->resolve(new Request(query: ['action' => 'ensure-sample-registry']));

        self::assertNotNull($result);
        self::assertSame('ensure-sample-registry', $result->actionName);
        self::assertTrue($result->payload['created']);

        foreach ($repository->listStorageFiles() as $path) {
            @unlink($path);
        }
        @rmdir($repository->getStorageDirectoryPath());
        @rmdir($projectDir.'/resources/discovery');
        @rmdir($projectDir.'/resources');
        @rmdir($projectDir);
    }
}

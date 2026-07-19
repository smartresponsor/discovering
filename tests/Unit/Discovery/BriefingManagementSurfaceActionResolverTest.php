<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Briefing\BriefingManagementActionService;
use App\Discovering\Service\Discovery\Briefing\BriefingManagementSurfaceActionResolver;
use App\Discovering\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exercises the briefing management surface action resolver test case for the Discovering component.
 */
final class BriefingManagementSurfaceActionResolverTest extends DiscoveryTempFilesystemTestCase
{
    public function testItResolvesAuditRegistryAction(): void
    {
        $projectDir = $this->createTempDirectory('discovering-briefing-resolver-audit-');
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
        $projectDir = $this->createTempDirectory('discovering-briefing-resolver-seed-');
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
        @rmdir($projectDir.'/resources/discovery');
        @rmdir($projectDir.'/resources');
        @rmdir($projectDir);
    }
}

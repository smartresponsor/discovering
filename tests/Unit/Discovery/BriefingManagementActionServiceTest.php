<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Briefing\BriefingManagementActionService;
use App\Discovering\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the briefing management action service test case for the Discovering component.
 */
final class BriefingManagementActionServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testAuditRegistryReportsFilesAndRecords(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-action-audit-');
        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'alpha.json', [
            ['resourceId' => 'briefing-alpha', 'title' => 'Alpha', 'body' => 'Alpha body'],
        ]);

        $service = new BriefingManagementActionService(new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $result = $service->auditRegistry();

        self::assertSame('audit-registry', $result->actionName);
        self::assertSame(1, $result->payload['fileCount']);
        self::assertSame(1, $result->payload['recordCount']);
        self::assertFalse($result->payload['legacyExists']);
    }

    public function testEnsureSampleRegistrySeedsFilesWhenRegistryIsEmpty(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-action-seed-');
        $service = new BriefingManagementActionService(new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $result = $service->ensureSampleRegistry();
        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertSame('ensure-sample-registry', $result->actionName);
        self::assertTrue($result->payload['created']);
        self::assertCount(2, $repository->listStorageFiles());
        self::assertCount(2, $repository->all());
    }

    public function testMigrateLegacyStorageMovesLegacyFileIntoRegistry(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-action-legacy-');
        $this->writeLegacyDiscoveryRegistryFile($projectDir, 'briefing_source_records.json', [
            ['resourceId' => 'legacy-briefing', 'title' => 'Legacy', 'body' => 'Legacy body'],
        ]);

        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );
        $service = new BriefingManagementActionService($repository);

        $result = $service->migrateLegacyStorage();

        self::assertSame('migrate-legacy-storage', $result->actionName);
        self::assertTrue($result->payload['migrated']);
        self::assertFileExists($repository->getStoragePath());
        self::assertFileDoesNotExist($repository->getLegacyStoragePath());
        self::assertCount(1, $repository->all());
    }
}

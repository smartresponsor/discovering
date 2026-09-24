<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Service\Briefing\DiscoveryBriefingManagementActionService;
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

        $service = new DiscoveryBriefingManagementActionService(new DiscoveryBriefingFileSourceRecordRepository(
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
        $service = new DiscoveryBriefingManagementActionService(new DiscoveryBriefingFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $result = $service->ensureSampleRegistry();
        $repository = new DiscoveryBriefingFileSourceRecordRepository(
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

        $repository = new DiscoveryBriefingFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );
        $service = new DiscoveryBriefingManagementActionService($repository);

        $result = $service->migrateLegacyStorage();

        self::assertSame('migrate-legacy-storage', $result->actionName);
        self::assertTrue($result->payload['migrated']);
        self::assertFileExists($repository->getStoragePath());
        self::assertFileDoesNotExist($repository->getLegacyStoragePath());
        self::assertCount(1, $repository->all());
    }
}

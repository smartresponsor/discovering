<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Briefing\BriefingManagementActionService;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class BriefingManagementActionServiceTest extends TestCase
{
    public function testAuditRegistryReportsFilesAndRecords(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-action-audit-' . uniqid('', true);
        $storageDirectory = $projectDir . '/resources/discovery/briefings';
        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            ['resourceId' => 'briefing-alpha', 'title' => 'Alpha', 'body' => 'Alpha body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

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

        @unlink($storageDirectory . '/alpha.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testEnsureSampleRegistrySeedsFilesWhenRegistryIsEmpty(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-action-seed-' . uniqid('', true);
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

        foreach ($repository->listStorageFiles() as $path) {
            @unlink($path);
        }
        @rmdir($repository->getStorageDirectoryPath());
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testMigrateLegacyStorageMovesLegacyFileIntoRegistry(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-action-legacy-' . uniqid('', true);
        $legacyDirectory = $projectDir . '/resources/discovery';
        mkdir($legacyDirectory, 0777, true);
        file_put_contents($legacyDirectory . '/briefing_source_records.json', json_encode([
            ['resourceId' => 'legacy-briefing', 'title' => 'Legacy', 'body' => 'Legacy body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

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

        @unlink($repository->getStoragePath());
        @rmdir($repository->getStorageDirectoryPath());
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

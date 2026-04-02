<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Playbook\PlaybookManagementActionService;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class PlaybookManagementActionServiceTest extends TestCase
{
    public function testAuditRegistryReportsFilesAndRecords(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-action-audit-' . uniqid('', true);
        $storageDirectory = $projectDir . '/resources/discovery/playbooks';
        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            ['resourceId' => 'playbook-alpha', 'title' => 'Alpha', 'body' => 'Alpha body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $service = new PlaybookManagementActionService(new PlaybookFileDiscoverySourceRecordRepository(
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
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-action-seed-' . uniqid('', true);
        $service = new PlaybookManagementActionService(new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $result = $service->ensureSampleRegistry();
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
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
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-action-legacy-' . uniqid('', true);
        $legacyDirectory = $projectDir . '/resources/discovery';
        mkdir($legacyDirectory, 0777, true);
        file_put_contents($legacyDirectory . '/playbook_source_records.json', json_encode([
            ['resourceId' => 'legacy-playbook', 'title' => 'Legacy', 'body' => 'Legacy body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );
        $service = new PlaybookManagementActionService($repository);

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

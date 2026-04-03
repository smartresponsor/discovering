<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class PlaybookFileDiscoverySourceRecordRepositoryTest extends DiscoveryTempFilesystemTestCase
{
    public function testItAggregatesPlaybookRecordsFromDirectoryFiles(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-');
        $storageDirectory = $projectDir . '/resources/discovery/playbooks';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            [
                'resourceId' => 'playbook-alpha',
                'title' => 'Alpha playbook',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($storageDirectory . '/beta.json', json_encode([
            [
                'resourceId' => 'playbook-beta',
                'title' => 'Beta playbook',
                'body' => 'Beta body',
                'resourceType' => 'special-playbook',
                'filters' => ['status' => 'draft', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['beta']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $records = $repository->all();
        $paths = $repository->listStorageFiles();

        self::assertSame('playbook-file-source-provider', $repository->getSourceName());
        self::assertSame('playbook', $repository->getResourceType());
        self::assertCount(2, $records);
        self::assertCount(2, $paths);
        self::assertStringEndsWith('/resources/discovery/playbooks', $repository->getStorageDirectoryPath());
        self::assertSame('playbook-alpha', $records[0]->resourceId);
        self::assertSame('special-playbook', $records[1]->resourceType);

        @unlink($storageDirectory . '/alpha.json');
        @unlink($storageDirectory . '/beta.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItUsesLegacyFallbackWhenDirectoryDoesNotExist(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-legacy-');
        $legacyDirectory = $projectDir . '/resources/discovery';

        mkdir($legacyDirectory, 0777, true);
        file_put_contents($legacyDirectory . '/playbook_source_records.json', json_encode([
            [
                'resourceId' => 'legacy-playbook',
                'title' => 'Legacy playbook',
                'body' => 'Legacy body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertCount(1, $repository->all());
        self::assertSame([$repository->getLegacyStoragePath()], $repository->listStorageFiles());

        @unlink($legacyDirectory . '/playbook_source_records.json');
        @rmdir($legacyDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItExportsAndReplacesPlaybookRecords(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-export-');
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $importPath = $projectDir . '/import.json';
        if (!is_dir($projectDir)) {
            mkdir($projectDir, 0777, true);
        }

        file_put_contents($importPath, json_encode([
            [
                'resourceId' => 'playbook-gamma',
                'title' => 'Gamma playbook',
                'body' => 'Gamma body',
                'filters' => ['status' => 'active'],
                'metadata' => ['tags' => ['gamma']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $importedCount = $repository->importFile($importPath);
        $exportedJson = $repository->exportJson();

        self::assertSame(1, $importedCount);
        self::assertStringContainsString('"resourceId": "playbook-gamma"', $exportedJson);
        self::assertFileExists($repository->getStoragePath());

        @unlink($importPath);
        @unlink($repository->getStoragePath());
        @rmdir(dirname($repository->getStoragePath()));
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItReturnsEmptyListWhenNoStorageExists(): void
    {
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $this->createTempDirectory('discovering-playbook-missing-'),
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertSame([], $repository->all());
    }
}

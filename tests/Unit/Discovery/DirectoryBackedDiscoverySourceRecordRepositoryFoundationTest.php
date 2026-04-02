<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class DirectoryBackedDiscoverySourceRecordRepositoryFoundationTest extends TestCase
{
    public function testItProvidesSharedDirectoryBackedLifecycleBehavior(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-foundation-' . uniqid('', true);
        $repository = new class($projectDir, new DiscoverySourceRecordJsonFileDecoder(), new DiscoverySourceRecordJsonFileEncoder()) extends AbstractDirectoryBackedDiscoverySourceRecordRepository {
            public function getSourceName(): string
            {
                return 'test-file-source-provider';
            }

            public function getResourceType(): string
            {
                return 'test';
            }

            public function getStorageDirectoryPath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/tests';
            }

            public function getStoragePath(): string
            {
                return $this->getStorageDirectoryPath() . '/test_source_records.json';
            }

            public function getLegacyStoragePath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/test_source_records.json';
            }
        };

        $repository->replaceAll([
            new DiscoverySourceRecord(
                resourceType: 'test',
                resourceId: 'test-alpha',
                title: 'Alpha',
                body: 'Alpha body',
                filters: ['status' => 'active'],
                metadata: ['tags' => ['alpha']],
            ),
        ]);

        self::assertFileExists($repository->getStoragePath());
        self::assertCount(1, $repository->listStorageFiles());
        self::assertCount(1, $repository->all());
        self::assertStringContainsString('"resourceId": "test-alpha"', $repository->exportJson());

        $importPath = $projectDir . '/import.json';
        file_put_contents($importPath, json_encode([
            [
                'resourceId' => 'test-beta',
                'title' => 'Beta',
                'body' => 'Beta body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $importedCount = $repository->importFile($importPath, $repository->getStorageDirectoryPath() . '/beta.json');

        self::assertSame(1, $importedCount);
        self::assertCount(2, $repository->listStorageFiles());
        self::assertCount(2, $repository->all());

        @unlink($importPath);
        foreach ($repository->listStorageFiles() as $path) {
            @unlink($path);
        }
        @rmdir($repository->getStorageDirectoryPath());
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItFallsBackToLegacyFileWhenDirectoryIsMissing(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-foundation-legacy-' . uniqid('', true);
        $legacyDirectory = $projectDir . '/resources/discovery';
        mkdir($legacyDirectory, 0777, true);
        file_put_contents($legacyDirectory . '/test_source_records.json', json_encode([
            [
                'resourceId' => 'legacy-alpha',
                'title' => 'Legacy Alpha',
                'body' => 'Legacy body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new class($projectDir, new DiscoverySourceRecordJsonFileDecoder(), new DiscoverySourceRecordJsonFileEncoder()) extends AbstractDirectoryBackedDiscoverySourceRecordRepository {
            public function getSourceName(): string
            {
                return 'test-file-source-provider';
            }

            public function getResourceType(): string
            {
                return 'test';
            }

            public function getStorageDirectoryPath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/tests';
            }

            public function getStoragePath(): string
            {
                return $this->getStorageDirectoryPath() . '/test_source_records.json';
            }

            public function getLegacyStoragePath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/test_source_records.json';
            }
        };

        self::assertSame([$repository->getLegacyStoragePath()], $repository->listStorageFiles());
        self::assertCount(1, $repository->all());

        @unlink($repository->getLegacyStoragePath());
        @rmdir($legacyDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the directory backed family management surface builder test case for the Discovering component.
 */
final class DirectoryBackedFamilyManagementSurfaceBuilderTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsReusableManagementSurfaceFromDirectoryBackedRepository(): void
    {
        $projectDir = $this->createTempDirectory('discovering-family-surface-');
        $storageDirectory = $projectDir . '/resources/discovery/family';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            [
                'resourceId' => 'family-alpha',
                'title' => 'Alpha family record',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha', 'ops']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($storageDirectory . '/beta.json', json_encode([
            [
                'resourceId' => 'family-beta',
                'title' => 'Beta family record',
                'body' => 'Beta body',
                'filters' => ['status' => 'draft', 'visibility' => 'public'],
                'metadata' => ['tags' => ['beta']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new class($projectDir, new DiscoverySourceRecordJsonFileDecoder(), new DiscoverySourceRecordJsonFileEncoder()) extends AbstractDirectoryBackedDiscoverySourceRecordRepository {
            public function getSourceName(): string
            {
                return 'family-file-source-provider';
            }

            public function getResourceType(): string
            {
                return 'family';
            }

            public function getStorageDirectoryPath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/family';
            }

            public function getStoragePath(): string
            {
                return $this->getStorageDirectoryPath() . '/family_source_records.json';
            }

            public function getLegacyStoragePath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/family_source_records.json';
            }
        };

        $surface = (new DirectoryBackedFamilyManagementSurfaceBuilder())->build($repository);

        self::assertSame('family-file-source-provider', $surface->sourceName);
        self::assertSame($repository->getStorageDirectoryPath(), $surface->storageDirectoryPath);
        self::assertSame(2, $surface->totalRecords);
        self::assertSame(2, $surface->totalFiles);
        self::assertSame('family-alpha', $surface->entries[0]->resourceId);
        self::assertSame('active', $surface->entries[0]->status);
        self::assertSame(['alpha', 'ops'], $surface->entries[0]->tags);
        self::assertSame('alpha.json', $surface->fileEntries[0]->fileName);
        self::assertSame(1, $surface->fileEntries[0]->recordCount);

        @unlink($storageDirectory . '/alpha.json');
        @unlink($storageDirectory . '/beta.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

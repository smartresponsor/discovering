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
        $projectDir = $this->createTempProjectDirectory('discovering-playbook-');

        $this->writeDiscoveryRegistryFile($projectDir, 'playbooks', 'alpha.json', [
            [
                'resourceId' => 'playbook-alpha',
                'title' => 'Alpha playbook',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ]);
        $this->writeDiscoveryRegistryFile($projectDir, 'playbooks', 'beta.json', [
            [
                'resourceId' => 'playbook-beta',
                'title' => 'Beta playbook',
                'body' => 'Beta body',
                'resourceType' => 'special-playbook',
                'filters' => ['status' => 'draft', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['beta']],
            ],
        ]);

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
    }

    public function testItUsesLegacyFallbackWhenDirectoryDoesNotExist(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-playbook-legacy-');

        $this->writeLegacyDiscoveryRegistryFile($projectDir, 'playbook_source_records.json', [
            [
                'resourceId' => 'legacy-playbook',
                'title' => 'Legacy playbook',
                'body' => 'Legacy body',
            ],
        ]);

        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertCount(1, $repository->all());
        self::assertSame([$repository->getLegacyStoragePath()], $repository->listStorageFiles());
    }

    public function testItExportsAndReplacesPlaybookRecords(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-playbook-export-');
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $importPath = $projectDir . '/import.json';
        $this->writeJsonFile($importPath, [
            [
                'resourceId' => 'playbook-gamma',
                'title' => 'Gamma playbook',
                'body' => 'Gamma body',
                'filters' => ['status' => 'active'],
                'metadata' => ['tags' => ['gamma']],
            ],
        ]);

        $importedCount = $repository->importFile($importPath);
        $exportedJson = $repository->exportJson();

        self::assertSame(1, $importedCount);
        self::assertStringContainsString('"resourceId": "playbook-gamma"', $exportedJson);
        self::assertFileExists($repository->getStoragePath());
    }

    public function testItReturnsEmptyListWhenNoStorageExists(): void
    {
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $this->createTempProjectDirectory('discovering-playbook-missing-'),
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertSame([], $repository->all());
    }
}

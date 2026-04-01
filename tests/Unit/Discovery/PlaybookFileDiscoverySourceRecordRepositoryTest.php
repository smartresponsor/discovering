<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class PlaybookFileDiscoverySourceRecordRepositoryTest extends TestCase
{
    public function testItLoadsPlaybookRecordsFromJsonFile(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-' . uniqid('', true);
        $resourceDirectory = $projectDir . '/resources/discovery';

        mkdir($resourceDirectory, 0777, true);
        file_put_contents($resourceDirectory . '/playbook_source_records.json', json_encode([
            [
                'resourceId' => 'playbook-alpha',
                'title' => 'Alpha playbook',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha']],
            ],
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

        self::assertSame('playbook-file-source-provider', $repository->getSourceName());
        self::assertSame('playbook', $repository->getResourceType());
        self::assertCount(2, $records);
        self::assertSame('playbook-alpha', $records[0]->resourceId);
        self::assertSame('playbook', $records[0]->resourceType);
        self::assertSame('special-playbook', $records[1]->resourceType);

        @unlink($resourceDirectory . '/playbook_source_records.json');
        @rmdir($resourceDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItExportsAndReplacesPlaybookRecords(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-export-' . uniqid('', true);
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
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItReturnsEmptyListWhenJsonFileDoesNotExist(): void
    {
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            sys_get_temp_dir() . '/discovering-playbook-missing-' . uniqid('', true),
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertSame([], $repository->all());
    }
}

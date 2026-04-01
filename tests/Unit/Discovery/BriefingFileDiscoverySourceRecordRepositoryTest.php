<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class BriefingFileDiscoverySourceRecordRepositoryTest extends TestCase
{
    public function testItAggregatesBriefingRecordsFromDirectoryFiles(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-' . uniqid('', true);
        $storageDirectory = $projectDir . '/resources/discovery/briefings';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            [
                'resourceId' => 'briefing-alpha',
                'title' => 'Alpha briefing',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($storageDirectory . '/beta.json', json_encode([
            [
                'resourceId' => 'briefing-beta',
                'title' => 'Beta briefing',
                'body' => 'Beta body',
                'resourceType' => 'special-briefing',
                'filters' => ['status' => 'draft', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['beta']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $records = $repository->all();
        $paths = $repository->listStorageFiles();

        self::assertSame('briefing-file-source-provider', $repository->getSourceName());
        self::assertSame('briefing', $repository->getResourceType());
        self::assertCount(2, $records);
        self::assertCount(2, $paths);
        self::assertStringEndsWith('/resources/discovery/briefings', $repository->getStorageDirectoryPath());
        self::assertSame('briefing-alpha', $records[0]->resourceId);
        self::assertSame('special-briefing', $records[1]->resourceType);

        @unlink($storageDirectory . '/alpha.json');
        @unlink($storageDirectory . '/beta.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItUsesLegacyFallbackWhenDirectoryDoesNotExist(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-legacy-' . uniqid('', true);
        $legacyDirectory = $projectDir . '/resources/discovery';

        mkdir($legacyDirectory, 0777, true);
        file_put_contents($legacyDirectory . '/briefing_source_records.json', json_encode([
            [
                'resourceId' => 'legacy-briefing',
                'title' => 'Legacy briefing',
                'body' => 'Legacy body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertCount(1, $repository->all());
        self::assertSame([$repository->getLegacyStoragePath()], $repository->listStorageFiles());

        @unlink($legacyDirectory . '/briefing_source_records.json');
        @rmdir($legacyDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

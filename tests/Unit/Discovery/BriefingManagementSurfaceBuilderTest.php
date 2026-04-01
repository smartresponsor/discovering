<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Briefing\BriefingManagementSurfaceBuilder;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class BriefingManagementSurfaceBuilderTest extends TestCase
{
    public function testItBuildsManagementSurfaceFromLiveBriefingRecords(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-briefing-surface-' . uniqid('', true);
        $storageDirectory = $projectDir . '/resources/discovery/briefings';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/zeta.json', json_encode([
            [
                'resourceId' => 'briefing-zeta',
                'title' => 'Zeta briefing',
                'body' => 'Zeta body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['zeta', 'ops']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            [
                'resourceId' => 'briefing-alpha',
                'title' => 'Alpha briefing',
                'body' => 'Alpha body',
                'filters' => ['status' => 'draft', 'visibility' => 'public'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $surface = (new BriefingManagementSurfaceBuilder($repository))->build();

        self::assertSame('briefing-file-source-provider', $surface->sourceName);
        self::assertSame($repository->getStorageDirectoryPath(), $surface->storageDirectoryPath);
        self::assertSame(2, $surface->totalRecords);
        self::assertSame(2, $surface->totalFiles);
        self::assertSame('briefing-alpha', $surface->entries[0]->resourceId);
        self::assertSame('draft', $surface->entries[0]->status);
        self::assertSame('public', $surface->entries[0]->visibility);
        self::assertSame(['alpha'], $surface->entries[0]->tags);
        self::assertSame('alpha.json', $surface->fileEntries[0]->fileName);
        self::assertSame(1, $surface->fileEntries[0]->recordCount);
        self::assertSame('zeta.json', $surface->fileEntries[1]->fileName);

        @unlink($storageDirectory . '/alpha.json');
        @unlink($storageDirectory . '/zeta.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

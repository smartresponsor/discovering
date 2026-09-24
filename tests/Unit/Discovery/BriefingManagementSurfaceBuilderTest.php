<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Briefing\DiscoveryBriefingManagementSurfaceBuilder;
use App\Discovering\Builder\Support\DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the briefing management surface builder test case for the Discovering component.
 */
final class BriefingManagementSurfaceBuilderTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsManagementSurfaceFromLiveBriefingRecords(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-surface-');

        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'zeta.json', [
            [
                'resourceId' => 'briefing-zeta',
                'title' => 'Zeta briefing',
                'body' => 'Zeta body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['zeta', 'ops']],
            ],
        ]);
        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'alpha.json', [
            [
                'resourceId' => 'briefing-alpha',
                'title' => 'Alpha briefing',
                'body' => 'Alpha body',
                'filters' => ['status' => 'draft', 'visibility' => 'public'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ]);

        $repository = new DiscoveryBriefingFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $surface = (new DiscoveryBriefingManagementSurfaceBuilder(
            $repository,
            new DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder(),
        ))->build();

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
    }
}

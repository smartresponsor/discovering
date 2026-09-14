<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the briefing file discovery source record repository test case for the Discovering component.
 */
final class BriefingFileDiscoverySourceRecordRepositoryTest extends DiscoveryTempFilesystemTestCase
{
    public function testItAggregatesBriefingRecordsFromDirectoryFiles(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-');

        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'alpha.json', [
            [
                'resourceId' => 'briefing-alpha',
                'title' => 'Alpha briefing',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ]);
        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'beta.json', [
            [
                'resourceId' => 'briefing-beta',
                'title' => 'Beta briefing',
                'body' => 'Beta body',
                'resourceType' => 'special-briefing',
                'filters' => ['status' => 'draft', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['beta']],
            ],
        ]);

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
    }

    public function testItUsesLegacyFallbackWhenDirectoryDoesNotExist(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-legacy-');

        $this->writeLegacyDiscoveryRegistryFile($projectDir, 'briefing_source_records.json', [
            [
                'resourceId' => 'legacy-briefing',
                'title' => 'Legacy briefing',
                'body' => 'Legacy body',
            ],
        ]);

        $repository = new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        self::assertCount(1, $repository->all());
        self::assertSame([$repository->getLegacyStoragePath()], $repository->listStorageFiles());
    }
}

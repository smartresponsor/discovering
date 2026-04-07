<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Playbook\PlaybookManagementSurfaceBuilder;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the playbook management surface builder test case for the Discovering component.
 */
final class PlaybookManagementSurfaceBuilderTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsManagementSurfaceFromLivePlaybookRecords(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-playbook-surface-');

        $this->writeDiscoveryRegistryFile($projectDir, 'playbooks', 'zeta.json', [
            [
                'resourceId' => 'playbook-zeta',
                'title' => 'Zeta playbook',
                'body' => 'Zeta body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['zeta', 'ops']],
            ],
        ]);
        $this->writeDiscoveryRegistryFile($projectDir, 'playbooks', 'alpha.json', [
            [
                'resourceId' => 'playbook-alpha',
                'title' => 'Alpha playbook',
                'body' => 'Alpha body',
                'filters' => ['status' => 'draft', 'visibility' => 'public'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ]);

        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        );

        $surface = (new PlaybookManagementSurfaceBuilder($repository))->build();

        self::assertSame('playbook-file-source-provider', $surface->sourceName);
        self::assertSame($repository->getStorageDirectoryPath(), $surface->storageDirectoryPath);
        self::assertSame(2, $surface->totalRecords);
        self::assertSame(2, $surface->totalFiles);
        self::assertSame('playbook-alpha', $surface->entries[0]->resourceId);
        self::assertSame('draft', $surface->entries[0]->status);
        self::assertSame('public', $surface->entries[0]->visibility);
        self::assertSame(['alpha'], $surface->entries[0]->tags);
        self::assertSame('alpha.json', $surface->fileEntries[0]->fileName);
        self::assertSame(1, $surface->fileEntries[0]->recordCount);
        self::assertSame('zeta.json', $surface->fileEntries[1]->fileName);
    }
}

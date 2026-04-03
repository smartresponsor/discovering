<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Playbook\PlaybookManagementSurfaceBuilder;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class PlaybookManagementSurfaceBuilderTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsManagementSurfaceFromLivePlaybookRecords(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-surface-');
        $storageDirectory = $projectDir . '/resources/discovery/playbooks';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/zeta.json', json_encode([
            [
                'resourceId' => 'playbook-zeta',
                'title' => 'Zeta playbook',
                'body' => 'Zeta body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['zeta', 'ops']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            [
                'resourceId' => 'playbook-alpha',
                'title' => 'Alpha playbook',
                'body' => 'Alpha body',
                'filters' => ['status' => 'draft', 'visibility' => 'public'],
                'metadata' => ['tags' => ['alpha']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

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

        @unlink($storageDirectory . '/alpha.json');
        @unlink($storageDirectory . '/zeta.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Playbook\PlaybookManagementSurfaceBuilder;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class PlaybookManagementSurfaceBuilderTest extends TestCase
{
    public function testItBuildsManagementSurfaceFromLivePlaybookRecords(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-surface-' . uniqid('', true);
        $resourceDirectory = $projectDir . '/resources/discovery';

        mkdir($resourceDirectory, 0777, true);
        file_put_contents($resourceDirectory . '/playbook_source_records.json', json_encode([
            [
                'resourceId' => 'playbook-zeta',
                'title' => 'Zeta playbook',
                'body' => 'Zeta body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['zeta', 'ops']],
            ],
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
        self::assertSame($repository->getStoragePath(), $surface->storagePath);
        self::assertSame(2, $surface->totalRecords);
        self::assertSame('playbook-alpha', $surface->entries[0]->resourceId);
        self::assertSame('draft', $surface->entries[0]->status);
        self::assertSame('public', $surface->entries[0]->visibility);
        self::assertSame(['alpha'], $surface->entries[0]->tags);
        self::assertSame('playbook-zeta', $surface->entries[1]->resourceId);

        @unlink($resourceDirectory . '/playbook_source_records.json');
        @rmdir($resourceDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

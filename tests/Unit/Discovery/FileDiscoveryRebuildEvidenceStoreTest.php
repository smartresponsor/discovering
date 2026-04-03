<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use App\Service\Discovery\Rebuild\DiscoveryRebuildEvidenceJsonSerializer;
use App\Service\Discovery\Rebuild\FileDiscoveryRebuildEvidenceStore;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class FileDiscoveryRebuildEvidenceStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testItPersistsAndReturnsLatestRebuildEvidence(): void
    {
        $directory = $this->createTempDirectory('discovering-rebuild-evidence-');
        mkdir($directory, 0o777, true);
        $path = $directory . '/rebuild-evidence.json';

        $store = new FileDiscoveryRebuildEvidenceStore($path, new DiscoveryRebuildEvidenceJsonSerializer());
        $store->append(new DiscoveryRebuildSummary(
            evidenceId: 'reb-1',
            resource: 'briefing',
            rebuildMode: 'full',
            backendName: 'sqlite-fts5',
            deploymentMode: 'in_place',
            zeroDowntimeReady: false,
            startedAt: '2026-04-03T00:00:00+00:00',
            finishedAt: '2026-04-03T00:00:01+00:00',
            candidateDocumentCount: 2,
            indexedDocumentCount: 1,
            skippedDocumentCount: 1,
            indexedCountsByResource: ['briefing' => 1, 'global' => 1],
        ));
        $store->append(new DiscoveryRebuildSummary(
            evidenceId: 'reb-2',
            resource: 'global',
            rebuildMode: 'full',
            backendName: 'sqlite-fts5',
            deploymentMode: 'in_place',
            zeroDowntimeReady: false,
            startedAt: '2026-04-03T00:10:00+00:00',
            finishedAt: '2026-04-03T00:10:02+00:00',
            candidateDocumentCount: 4,
            indexedDocumentCount: 4,
            skippedDocumentCount: 0,
            indexedCountsByResource: ['briefing' => 2, 'playbook' => 2, 'global' => 4],
        ));

        $latest = $store->latest();

        self::assertCount(2, $latest);
        self::assertSame('reb-2', $latest[0]->evidenceId);
        self::assertSame('reb-1', $latest[1]->evidenceId);
        self::assertSame(4, $latest[0]->indexedDocumentCount);
    }
}

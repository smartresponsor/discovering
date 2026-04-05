<?php

declare(strict_types=1);

namespace App\Tests\Contract\Discovery;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use PHPUnit\Framework\TestCase;

final class DiscoveryRebuildSummaryContractTest extends TestCase
{
    public function testItSerializesStagedRebuildFieldsInStableShape(): void
    {
        $summary = new DiscoveryRebuildSummary(
            evidenceId: 'reb-1234',
            resource: 'global',
            rebuildMode: 'full',
            backendName: 'sqlite-fts5',
            deploymentMode: 'staged_alias_swap',
            zeroDowntimeReady: true,
            startedAt: '2026-04-03T00:00:00+00:00',
            finishedAt: '2026-04-03T00:01:00+00:00',
            candidateDocumentCount: 10,
            indexedDocumentCount: 10,
            skippedDocumentCount: 0,
            indexedCountsByResource: ['global' => 10, 'briefing' => 3],
            stagedIndexes: ['global' => 'global__staged__reb_1234'],
            aliasSwapApplied: true,
        );

        self::assertSame([
            'evidenceId' => 'reb-1234',
            'resource' => 'global',
            'rebuildMode' => 'full',
            'backendName' => 'sqlite-fts5',
            'deploymentMode' => 'staged_alias_swap',
            'zeroDowntimeReady' => true,
            'startedAt' => '2026-04-03T00:00:00+00:00',
            'finishedAt' => '2026-04-03T00:01:00+00:00',
            'candidateDocumentCount' => 10,
            'indexedDocumentCount' => 10,
            'skippedDocumentCount' => 0,
            'indexedCountsByResource' => ['global' => 10, 'briefing' => 3],
            'stagedIndexes' => ['global' => 'global__staged__reb_1234'],
            'aliasSwapApplied' => true,
        ], $summary->toArray());
    }
}

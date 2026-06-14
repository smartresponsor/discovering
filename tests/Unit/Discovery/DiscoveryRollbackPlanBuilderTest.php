<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use App\Service\Discovery\Rebuild\DiscoveryRollbackPlanBuilder;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery rollback plan builder test case for the Discovering component.
 */
final class DiscoveryRollbackPlanBuilderTest extends TestCase
{
    public function testBuildReturnsNoEvidenceStatusWhenHistoryIsEmpty(): void
    {
        $builder = new DiscoveryRollbackPlanBuilder(new InMemoryDiscoveryRebuildEvidenceStore([]));

        $plan = $builder->build();

        self::assertFalse($plan->rollbackReady);
        self::assertSame('no_evidence', $plan->status);
        self::assertNull($plan->recommendedCommand);
    }

    public function testBuildReturnsReadyPlanWhenTwoDistinctGlobalStagedRebuildsExist(): void
    {
        $builder = new DiscoveryRollbackPlanBuilder(new InMemoryDiscoveryRebuildEvidenceStore([
            new DiscoveryRebuildSummary(
                evidenceId: 'reb-current',
                resource: 'global',
                rebuildMode: 'full',
                backendName: 'sqlite-fts5',
                deploymentMode: 'staged_alias_swap',
                zeroDowntimeReady: true,
                startedAt: '2026-04-03T00:00:00+00:00',
                finishedAt: '2026-04-03T00:00:10+00:00',
                candidateDocumentCount: 10,
                indexedDocumentCount: 10,
                skippedDocumentCount: 0,
                indexedCountsByResource: ['global' => 10],
                stagedIndexes: ['global' => 'discovering__reb_current'],
                aliasSwapApplied: true,
            ),
            new DiscoveryRebuildSummary(
                evidenceId: 'reb-previous',
                resource: 'global',
                rebuildMode: 'full',
                backendName: 'sqlite-fts5',
                deploymentMode: 'staged_alias_swap',
                zeroDowntimeReady: true,
                startedAt: '2026-04-03T00:00:00+00:00',
                finishedAt: '2026-04-03T00:00:10+00:00',
                candidateDocumentCount: 9,
                indexedDocumentCount: 9,
                skippedDocumentCount: 0,
                indexedCountsByResource: ['global' => 9],
                stagedIndexes: ['global' => 'discovering__reb_previous'],
                aliasSwapApplied: true,
            ),
        ]));

        $plan = $builder->build();

        self::assertTrue($plan->rollbackReady);
        self::assertSame('plan_ready', $plan->status);
        self::assertSame('reb-current', $plan->currentEvidenceId);
        self::assertSame('reb-previous', $plan->previousEvidenceId);
        self::assertSame('discovering__reb_current', $plan->currentPhysicalIndex);
        self::assertSame('discovering__reb_previous', $plan->rollbackTargetPhysicalIndex);
        self::assertSame('app:discovery:rollback:execute --current=reb-current --target=reb-previous', $plan->recommendedCommand);
    }

    public function testBuildReturnsNoPreviousCandidateWhenOnlyOneGlobalRebuildExists(): void
    {
        $builder = new DiscoveryRollbackPlanBuilder(new InMemoryDiscoveryRebuildEvidenceStore([
            new DiscoveryRebuildSummary(
                evidenceId: 'reb-current',
                resource: 'global',
                rebuildMode: 'full',
                backendName: 'sqlite-fts5',
                deploymentMode: 'staged_alias_swap',
                zeroDowntimeReady: true,
                startedAt: '2026-04-03T00:00:00+00:00',
                finishedAt: '2026-04-03T00:00:10+00:00',
                candidateDocumentCount: 10,
                indexedDocumentCount: 10,
                skippedDocumentCount: 0,
                indexedCountsByResource: ['global' => 10],
                stagedIndexes: ['global' => 'discovering__reb_current'],
                aliasSwapApplied: true,
            ),
        ]));

        $plan = $builder->build();

        self::assertFalse($plan->rollbackReady);
        self::assertSame('no_previous_candidate', $plan->status);
        self::assertSame('discovering__reb_current', $plan->currentPhysicalIndex);
        self::assertNull($plan->rollbackTargetPhysicalIndex);
    }
}

final class InMemoryDiscoveryRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    /**
     * @param list<DiscoveryRebuildSummary> $items
     */
    public function __construct(private array $items)
    {
    }

    public function append(DiscoveryRebuildSummary $summary): void
    {
        $this->items[] = $summary;
    }

    public function latest(int $limit = 20): array
    {
        return array_slice($this->items, 0, $limit);
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Rebuild\DiscoveryRollbackPlanBuilder;
use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\Service\Rollback\DiscoveryRollbackExecutor;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryStagingCapableBackendInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery rollback executor test case for the Discovering component.
 */
final class DiscoveryRollbackExecutorTest extends TestCase
{
    public function testExecutePromotesRollbackTargetWhenPlanIsReady(): void
    {
        $adapter = new InMemoryStagingDiscoveryBackend();
        $executor = new DiscoveryRollbackExecutor($this->readyPlanBuilder(), $adapter);

        $result = $executor->execute('reb-current', 'reb-previous');

        self::assertTrue($result->executed);
        self::assertSame('rollback_executed', $result->status);
        self::assertSame([['global', 'discovering__reb_previous']], $adapter->aliasSwaps);
    }

    public function testExecuteBlocksWhenExpectedEvidenceDoesNotMatchLatestPlan(): void
    {
        $adapter = new InMemoryStagingDiscoveryBackend();
        $executor = new DiscoveryRollbackExecutor($this->readyPlanBuilder(), $adapter);

        $result = $executor->execute('reb-stale', 'reb-previous');

        self::assertFalse($result->executed);
        self::assertSame('current_evidence_mismatch', $result->status);
        self::assertSame([], $adapter->aliasSwaps);
    }

    private function readyPlanBuilder(): DiscoveryRollbackPlanBuilder
    {
        return new DiscoveryRollbackPlanBuilder(new InMemoryDiscoveryRebuildEvidenceStoreForExecutor([
            new DiscoveryRebuildSummaryDTO(
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
            new DiscoveryRebuildSummaryDTO(
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
    }
}

final class InMemoryDiscoveryRebuildEvidenceStoreForExecutor implements DiscoveryRebuildEvidenceStoreInterface
{
    /** @param list<DiscoveryRebuildSummaryDTO> $items */
    public function __construct(private array $items)
    {
    }

    public function append(DiscoveryRebuildSummaryDTO $summary): void
    {
        $this->items[] = $summary;
    }

    public function latest(int $limit = 20): array
    {
        return array_slice($this->items, 0, $limit);
    }
}

final class InMemoryStagingDiscoveryBackend implements DiscoveryBackendInterface, DiscoveryStagingCapableBackendInterface
{
    /** @var list<array{0:string,1:string}> */
    public array $aliasSwaps = [];

    public function upsert(string $resource, string $id, array $document): void
    {
    }

    public function remove(string $resource, string $id): void
    {
    }

    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        return [];
    }

    public function createIndex(string $resource): void
    {
    }

    public function swapAlias(string $from, string $to): void
    {
        $this->aliasSwaps[] = [$from, $to];
    }

    public function getBackendName(): string
    {
        return 'in-memory-staging';
    }

    public function supportsStagedRebuild(): bool
    {
        return true;
    }
}

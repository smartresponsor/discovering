<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Diagnostics\DiscoveryPlatformDiagnosticsBuilder;
use App\Discovering\Builder\Rebuild\DiscoveryRollbackPlanBuilder;
use App\Discovering\Builder\Topology\DiscoveryStateTopologyBuilder;
use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryStagingCapableBackendInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery platform diagnostics builder test case for the Discovering component.
 */
final class DiscoveryPlatformDiagnosticsBuilderTest extends TestCase
{
    public function testBuildReflectsSingleNodeSqlitePosture(): void
    {
        $builder = new DiscoveryPlatformDiagnosticsBuilder(
            adapter: new class implements DiscoveryBackendInterface, DiscoveryStagingCapableBackendInterface {
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
                }

                public function getBackendName(): string
                {
                    return 'sqlite-fts5';
                }

                public function supportsStagedRebuild(): bool
                {
                    return true;
                }
            },
            stateTopologyBuilder: $this->topologyBuilder(),
            rollbackPlanBuilder: $this->rollbackPlanBuilder([]),
        );

        $diagnostics = $builder->build();

        self::assertSame('sqlite-fts5', $diagnostics->backendName);
        self::assertSame('sqlite', $diagnostics->indexStoreBackend);
        self::assertTrue($diagnostics->stagedRebuildSupported);
        self::assertFalse($diagnostics->distributedReady);
        self::assertSame('no_evidence', $diagnostics->rollbackStatus);
        self::assertSame('transitioning', $diagnostics->postureStatus);
        self::assertSame('high', $diagnostics->riskLevel);
        self::assertContains('discoveryIndex', $diagnostics->blockingStores);
        self::assertContains('Active discovery backend is sqlite-fts5.', $diagnostics->notes);
        self::assertStringContainsString('Complete stronger coordination for blocking stores', $diagnostics->recommendedAction);
    }

    public function testBuildRecognizesDistributedReadyMeiliPosture(): void
    {
        $builder = new DiscoveryPlatformDiagnosticsBuilder(
            adapter: new class implements DiscoveryBackendInterface, DiscoveryStagingCapableBackendInterface {
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
                }

                public function getBackendName(): string
                {
                    return 'meilisearch';
                }

                public function supportsStagedRebuild(): bool
                {
                    return false;
                }
            },
            stateTopologyBuilder: $this->topologyBuilder(
                indexBackend: 'meili',
                meiliUrl: 'http://meili.internal:7700',
                operationLogBackend: 'doctrine',
                rebuildEvidenceBackend: 'doctrine',
                libsourceEventLogBackend: 'doctrine',
                rateLimitBackend: 'doctrine',
            ),
            rollbackPlanBuilder: $this->rollbackPlanBuilder([
                new DiscoveryRebuildSummaryDTO(
                    evidenceId: 'reb-current',
                    resource: 'global',
                    rebuildMode: 'full',
                    backendName: 'meilisearch',
                    deploymentMode: 'staged_alias_swap',
                    zeroDowntimeReady: true,
                    startedAt: '2026-04-03T00:00:00+00:00',
                    finishedAt: '2026-04-03T00:01:00+00:00',
                    candidateDocumentCount: 10,
                    indexedDocumentCount: 10,
                    skippedDocumentCount: 0,
                    indexedCountsByResource: ['global' => 10],
                    stagedIndexes: ['global' => 'discovering_global_v2'],
                    aliasSwapApplied: true,
                ),
                new DiscoveryRebuildSummaryDTO(
                    evidenceId: 'reb-prev',
                    resource: 'global',
                    rebuildMode: 'full',
                    backendName: 'meilisearch',
                    deploymentMode: 'staged_alias_swap',
                    zeroDowntimeReady: true,
                    startedAt: '2026-04-02T00:00:00+00:00',
                    finishedAt: '2026-04-02T00:01:00+00:00',
                    candidateDocumentCount: 9,
                    indexedDocumentCount: 9,
                    skippedDocumentCount: 0,
                    indexedCountsByResource: ['global' => 9],
                    stagedIndexes: ['global' => 'discovering_global_v1'],
                    aliasSwapApplied: true,
                ),
            ]),
        );

        $diagnostics = $builder->build();

        self::assertSame('meilisearch', $diagnostics->backendName);
        self::assertSame('meilisearch', $diagnostics->indexStoreBackend);
        self::assertFalse($diagnostics->stagedRebuildSupported);
        self::assertTrue($diagnostics->distributedReady);
        self::assertTrue($diagnostics->rollbackReady);
        self::assertSame('plan_ready', $diagnostics->rollbackStatus);
        self::assertSame('degraded', $diagnostics->postureStatus);
        self::assertSame('medium', $diagnostics->riskLevel);
        self::assertSame(6, $diagnostics->coordinationReadyStoreCount);
        self::assertSame([], $diagnostics->blockingStores);
        self::assertContains('Distributed-ready coordination is configured, but rebuild cutover still depends on backend-specific promotion semantics.', $diagnostics->notes);
        self::assertStringContainsString('deployment cutover playbooks', $diagnostics->recommendedAction);
    }

    private function topologyBuilder(
        string $discoverySqlitePath = '/workspace/discovering/var/discovery/discovering.sqlite',
        string $indexBackend = 'sqlite',
        string $meiliUrl = '',
        string $meiliIndexPrefix = 'discovering',
        string $operationLogPath = '/workspace/discovering/var/discovery/discovery-operation-log.json',
        string $operationLogBackend = 'file',
        string $rebuildEvidencePath = '/workspace/discovering/var/discovery/discovery-rebuild-evidence.json',
        string $rebuildEvidenceBackend = 'file',
        string $libsourceEventLogPath = '/workspace/discovering/var/discovery/libsource-operator-event-log.json',
        string $libsourceEventLogBackend = 'file',
        string $rateLimitBackend = 'file',
        string $rateLimitStorePath = '/workspace/discovering/var/discovery/discovery-rate-limit.json',
    ): DiscoveryStateTopologyBuilder {
        return new DiscoveryStateTopologyBuilder(
            projectDir: '/workspace/discovering',
            discoverySqlitePath: $discoverySqlitePath,
            indexBackend: $indexBackend,
            meiliUrl: $meiliUrl,
            meiliIndexPrefix: $meiliIndexPrefix,
            operationLogPath: $operationLogPath,
            operationLogBackend: $operationLogBackend,
            rebuildEvidencePath: $rebuildEvidencePath,
            rebuildEvidenceBackend: $rebuildEvidenceBackend,
            libsourceEventLogPath: $libsourceEventLogPath,
            libsourceEventLogBackend: $libsourceEventLogBackend,
            rateLimitBackend: $rateLimitBackend,
            rateLimitStorePath: $rateLimitStorePath,
        );
    }

    /**
     * @param list<DiscoveryRebuildSummaryDTO> $summaries
     */
    private function rollbackPlanBuilder(array $summaries): DiscoveryRollbackPlanBuilder
    {
        return new DiscoveryRollbackPlanBuilder(new class($summaries) implements DiscoveryRebuildEvidenceStoreInterface {
            /** @param list<DiscoveryRebuildSummaryDTO> $summaries */
            public function __construct(private readonly array $summaries)
            {
            }

            public function append(DiscoveryRebuildSummaryDTO $summary): void
            {
            }

            public function latest(int $limit = 20): array
            {
                return array_slice($this->summaries, 0, $limit);
            }
        });
    }
}

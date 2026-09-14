<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Indexer;

use App\Discovering\Dto\Discovery\DiscoveryRebuildSummary;
use App\Discovering\Dto\Discovery\ReindexRequest;
use App\Discovering\Service\Discovery\Rebuild\DiscoveryStagedIndexNamer;
use App\Discovering\ServiceInterface\Discovery\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\Discovering\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use App\Discovering\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableBackendInterface;
use App\Discovering\ValueObject\Discovery\DiscoveryDocument;

/**
 * Coordinates discovery indexer operations for the discovery index lifecycle.
 */
final class DiscoveryIndexer implements DiscoveryIndexerInterface
{
    public function __construct(
        private readonly DiscoveryBackendInterface $adapter,
        private readonly DiscoveryDocumentProviderInterface $documentProvider,
        private readonly DiscoveryStagedIndexNamer $stagedIndexNamer = new DiscoveryStagedIndexNamer(),
    ) {
    }

    /**
     * Performs the rebuild operation for this discovery service.
     */
    public function rebuild(ReindexRequest $request): DiscoveryRebuildSummary
    {
        $startedAt = gmdate(DATE_ATOM);
        $evidenceId = 'reb-'.bin2hex(random_bytes(8));
        $documents = $this->documentProvider->provide();
        $targetResource = '' === $request->resource ? 'global' : $request->resource;
        $indexedCount = 0;
        $skippedCount = 0;
        $indexedCountsByResource = [];
        $stagedIndexes = [];
        $aliasSwapApplied = false;
        $deploymentMode = 'in_place';
        $zeroDowntimeReady = $this->supportsStagedRebuild($targetResource, $request);

        if ($zeroDowntimeReady) {
            $deploymentMode = 'staged_alias_swap';
            $logicalIndexes = $this->resolveLogicalIndexesForGlobalRebuild($documents);
            foreach ($logicalIndexes as $logicalIndex) {
                $stagedIndexes[$logicalIndex] = $this->stagedIndexNamer->forLogicalIndex($logicalIndex, $evidenceId);
                $this->adapter->createIndex($stagedIndexes[$logicalIndex]);
            }
        } else {
            $this->adapter->createIndex('global');
            if ('global' !== $targetResource) {
                $this->adapter->createIndex($targetResource);
            }
        }

        foreach ($documents as $document) {
            if ('global' !== $targetResource && $document->resource !== $targetResource) {
                ++$skippedCount;
                continue;
            }

            if ($zeroDowntimeReady) {
                $this->upsertToLogicalIndexMap($document, $stagedIndexes);
            } else {
                $this->upsert($document);
            }

            ++$indexedCount;
            $indexedCountsByResource['global'] = ($indexedCountsByResource['global'] ?? 0) + 1;
            if ('global' !== $document->resource) {
                $indexedCountsByResource[$document->resource] = ($indexedCountsByResource[$document->resource] ?? 0) + 1;
            }
        }

        if ($zeroDowntimeReady) {
            foreach ($stagedIndexes as $logicalIndex => $stagedIndex) {
                $this->adapter->swapAlias($logicalIndex, $stagedIndex);
            }
            $aliasSwapApplied = true;
        }

        return new DiscoveryRebuildSummary(
            evidenceId: $evidenceId,
            resource: $targetResource,
            rebuildMode: $request->rebuildMode,
            backendName: $this->adapter->getBackendName(),
            deploymentMode: $deploymentMode,
            zeroDowntimeReady: $zeroDowntimeReady,
            startedAt: $startedAt,
            finishedAt: gmdate(DATE_ATOM),
            candidateDocumentCount: count($documents),
            indexedDocumentCount: $indexedCount,
            skippedDocumentCount: $skippedCount,
            indexedCountsByResource: $indexedCountsByResource,
            stagedIndexes: $stagedIndexes,
            aliasSwapApplied: $aliasSwapApplied,
        );
    }

    /**
     * Performs the upsert operation for this discovery service.
     */
    public function upsert(DiscoveryDocument $document): void
    {
        $payload = $document->toArray();
        $this->adapter->upsert('global', $document->id, $payload);

        if ('global' !== $document->resource) {
            $this->adapter->upsert($document->resource, $document->id, $payload);
        }
    }

    /**
     * Performs the remove operation for this discovery service.
     */
    public function remove(string $resource, string $id): void
    {
        $this->adapter->remove($resource, $id);
        if ('global' !== $resource) {
            $this->adapter->remove('global', $id);
        }
    }

    /**
     * @param array<string, string> $indexMap
     */
    private function upsertToLogicalIndexMap(DiscoveryDocument $document, array $indexMap): void
    {
        $payload = $document->toArray();
        $this->adapter->upsert($indexMap['global'] ?? 'global', $document->id, $payload);

        if ('global' !== $document->resource) {
            $this->adapter->upsert($indexMap[$document->resource] ?? $document->resource, $document->id, $payload);
        }
    }

    /**
     * @param list<DiscoveryDocument> $documents
     *
     * @return list<string>
     */
    private function resolveLogicalIndexesForGlobalRebuild(array $documents): array
    {
        $logicalIndexes = ['global'];
        foreach ($documents as $document) {
            if ('global' === $document->resource) {
                continue;
            }

            $logicalIndexes[$document->resource] = $document->resource;
        }

        return array_values($logicalIndexes);
    }

    private function supportsStagedRebuild(string $targetResource, ReindexRequest $request): bool
    {
        if ('in_place' === $request->deploymentMode) {
            return false;
        }

        if ('auto' !== $request->deploymentMode && 'staged_alias_swap' !== $request->deploymentMode) {
            return false;
        }

        if ('global' !== $targetResource) {
            return false;
        }

        return $this->adapter instanceof DiscoveryStagingCapableBackendInterface
            && $this->adapter->supportsStagedRebuild();
    }
}

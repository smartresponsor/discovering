<?php

declare(strict_types=1);

namespace App\Service\Discovery\Indexer;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use App\ValueObject\Discovery\DiscoveryDocument;

final class DiscoveryIndexer implements DiscoveryIndexerInterface
{
    public function __construct(
        private readonly DiscoveryAdapterInterface $adapter,
        private readonly DiscoveryDocumentProviderInterface $documentProvider,
    ) {
    }

    public function rebuild(ReindexRequest $request): DiscoveryRebuildSummary
    {
        $startedAt = gmdate(DATE_ATOM);
        $documents = $this->documentProvider->provide();
        $targetResource = $request->resource === '' ? 'global' : $request->resource;
        $indexedCount = 0;
        $skippedCount = 0;
        $indexedCountsByResource = [];

        $this->adapter->createIndex('global');
        if ($targetResource !== 'global') {
            $this->adapter->createIndex($targetResource);
        }

        foreach ($documents as $document) {
            if ($targetResource !== 'global' && $document->resource !== $targetResource) {
                ++$skippedCount;
                continue;
            }

            $this->upsert($document);
            ++$indexedCount;
            $indexedCountsByResource['global'] = ($indexedCountsByResource['global'] ?? 0) + 1;
            if ($document->resource !== 'global') {
                $indexedCountsByResource[$document->resource] = ($indexedCountsByResource[$document->resource] ?? 0) + 1;
            }
        }

        return new DiscoveryRebuildSummary(
            evidenceId: 'reb-' . bin2hex(random_bytes(8)),
            resource: $targetResource,
            rebuildMode: $request->rebuildMode,
            backendName: $this->adapter->getBackendName(),
            deploymentMode: 'in_place',
            zeroDowntimeReady: false,
            startedAt: $startedAt,
            finishedAt: gmdate(DATE_ATOM),
            candidateDocumentCount: count($documents),
            indexedDocumentCount: $indexedCount,
            skippedDocumentCount: $skippedCount,
            indexedCountsByResource: $indexedCountsByResource,
        );
    }

    public function upsert(DiscoveryDocument $document): void
    {
        $payload = $document->toArray();
        $this->adapter->upsert('global', $document->id, $payload);

        if ($document->resource !== 'global') {
            $this->adapter->upsert($document->resource, $document->id, $payload);
        }
    }

    public function remove(string $resource, string $id): void
    {
        $this->adapter->remove($resource, $id);
        if ($resource !== 'global') {
            $this->adapter->remove('global', $id);
        }
    }
}

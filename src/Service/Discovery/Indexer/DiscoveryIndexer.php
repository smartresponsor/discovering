<?php

declare(strict_types=1);

namespace App\Service\Discovery\Indexer;

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

    public function rebuild(ReindexRequest $request): void
    {
        $documents = $this->documentProvider->provide();
        $targetResource = $request->resource === '' ? 'global' : $request->resource;

        $this->adapter->createIndex('global');
        if ($targetResource !== 'global') {
            $this->adapter->createIndex($targetResource);
        }

        foreach ($documents as $document) {
            if ($targetResource !== 'global' && $document->resource !== $targetResource) {
                continue;
            }

            $this->upsert($document);
        }
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

<?php
declare(strict_types=1);

namespace App\Service\Discovery\Indexer;

use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use App\ValueObject\Discovery\DiscoveryDocument;

final class DiscoveryIndexer implements DiscoveryIndexerInterface
{
    public function __construct(private readonly DiscoveryAdapterInterface $adapter)
    {
    }

    public function rebuild(ReindexRequest $request): void
    {
        $this->adapter->createIndex($request->resource);
    }

    public function upsert(DiscoveryDocument $document): void
    {
        $this->adapter->upsert($document->resource, $document->id, $document->toArray());
    }

    public function remove(string $resource, string $id): void
    {
        $this->adapter->remove($resource, $id);
    }
}

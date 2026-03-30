<?php

declare(strict_types=1);

namespace App\Service\Discovery\Indexer;

use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;

final class DiscoveryIndexer implements DiscoveryIndexerInterface
{
    public function __construct(
        private readonly DiscoveryAdapterInterface $adapter,
    ) {
    }

    public function rebuild(ReindexRequest $request): void
    {
        $this->adapter->rebuild($request->resourceType);
    }
}

<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Indexer;

use App\Dto\Discovery\ReindexRequest;

interface DiscoveryIndexerInterface
{
    public function rebuild(ReindexRequest $request): void;
}

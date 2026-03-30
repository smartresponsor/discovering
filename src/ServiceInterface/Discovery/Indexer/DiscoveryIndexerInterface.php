<?php
declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Indexer;

use App\Dto\Discovery\ReindexRequest;
use App\ValueObject\Discovery\DiscoveryDocument;

interface DiscoveryIndexerInterface
{
    public function rebuild(ReindexRequest $request): void;
    public function upsert(DiscoveryDocument $document): void;
    public function remove(string $resource, string $id): void;
}

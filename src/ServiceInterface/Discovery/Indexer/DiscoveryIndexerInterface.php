<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Discovery\Indexer;

use App\Discovering\Dto\Discovery\DiscoveryRebuildSummary;
use App\Discovering\Dto\Discovery\ReindexRequest;
use App\Discovering\ValueObject\Discovery\DiscoveryDocument;

/**
 * Defines the contract for the discovery indexer capability within the discovery component.
 */
interface DiscoveryIndexerInterface
{
    /**
     * Performs the rebuild operation defined by this discovery contract.
     */
    public function rebuild(ReindexRequest $request): DiscoveryRebuildSummary;

    public function upsert(DiscoveryDocument $document): void;

    /**
     * Performs the remove operation defined by this discovery contract.
     */
    public function remove(string $resource, string $id): void;
}

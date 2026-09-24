<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Indexer;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\DTO\DiscoveryReindexRequestDTO;
use App\Discovering\ValueObject\DiscoveryDocument;

/**
 * Defines the contract for the discovery indexer capability within the discovery component.
 */
interface DiscoveryIndexerInterface
{
    /**
     * Performs the rebuild operation defined by this discovery contract.
     */
    public function rebuild(DiscoveryReindexRequestDTO $request): DiscoveryRebuildSummaryDTO;

    public function upsert(DiscoveryDocument $document): void;

    /**
     * Performs the remove operation defined by this discovery contract.
     */
    public function remove(string $resource, string $id): void;
}

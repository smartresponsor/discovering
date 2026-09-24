<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Rebuild;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;

/**
 * Defines the contract for the discovery rebuild evidence store capability within the discovery component.
 */
interface DiscoveryRebuildEvidenceStoreInterface
{
    /**
     * Performs the append operation defined by this discovery contract.
     */
    public function append(DiscoveryRebuildSummaryDTO $summary): void;

    /**
     * @return list<DiscoveryRebuildSummaryDTO>
     */
    public function latest(int $limit = 20): array;
}

<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;


/**
 * Defines the contract for the discovery rebuild evidence store capability within the discovery component.
 */
interface DiscoveryRebuildEvidenceStoreInterface
{
    /**
     * Performs the append operation defined by this discovery contract.
     */
    public function append(DiscoveryRebuildSummary $summary): void;

    /**
     * @return list<DiscoveryRebuildSummary>
     */
    public function latest(int $limit = 20): array;
}

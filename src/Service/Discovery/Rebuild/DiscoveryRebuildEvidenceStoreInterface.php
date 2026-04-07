<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;

interface DiscoveryRebuildEvidenceStoreInterface
{
    public function append(DiscoveryRebuildSummary $summary): void;

    /**
     * @return list<DiscoveryRebuildSummary>
     */
    public function latest(int $limit = 20): array;
}

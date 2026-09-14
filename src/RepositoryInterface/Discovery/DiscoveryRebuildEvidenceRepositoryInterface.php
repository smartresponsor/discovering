<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface\Discovery;

use App\Discovering\Entity\Discovery\DiscoveryRebuildEvidenceEntity;

/**
 * Defines persistence operations for discovery rebuild evidence.
 */
interface DiscoveryRebuildEvidenceRepositoryInterface
{
    public function save(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void;
}

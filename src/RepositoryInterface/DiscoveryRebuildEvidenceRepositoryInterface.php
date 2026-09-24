<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface;

use App\Discovering\Entity\DiscoveryRebuildEvidenceEntity;

/**
 * Defines persistence operations for discovery rebuild evidence.
 */
interface DiscoveryRebuildEvidenceRepositoryInterface
{
    public function save(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void;
}

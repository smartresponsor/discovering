<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface;

use App\Discovering\Entity\DiscoverySearchLogEntity;

/**
 * Defines persistence operations for discovery search-log records.
 */
interface DiscoverySearchLogRepositoryInterface
{
    public function save(DiscoverySearchLogEntity $entity, bool $flush = false): void;

    public function remove(DiscoverySearchLogEntity $entity, bool $flush = false): void;
}

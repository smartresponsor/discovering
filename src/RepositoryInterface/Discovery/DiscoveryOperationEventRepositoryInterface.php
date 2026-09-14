<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface\Discovery;

use App\Discovering\Entity\Discovery\DiscoveryOperationEventEntity;

/**
 * Defines persistence operations for discovery operation events.
 */
interface DiscoveryOperationEventRepositoryInterface
{
    public function save(DiscoveryOperationEventEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryOperationEventEntity $entity, bool $flush = false): void;
}

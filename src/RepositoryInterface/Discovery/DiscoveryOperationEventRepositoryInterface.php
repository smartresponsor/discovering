<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoveryOperationEventEntity;

interface DiscoveryOperationEventRepositoryInterface
{
    public function save(DiscoveryOperationEventEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryOperationEventEntity $entity, bool $flush = false): void;
}

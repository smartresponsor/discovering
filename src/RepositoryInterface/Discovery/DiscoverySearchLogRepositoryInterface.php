<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoverySearchLogEntity;

interface DiscoverySearchLogRepositoryInterface
{
    public function save(DiscoverySearchLogEntity $entity, bool $flush = false): void;

    public function remove(DiscoverySearchLogEntity $entity, bool $flush = false): void;
}

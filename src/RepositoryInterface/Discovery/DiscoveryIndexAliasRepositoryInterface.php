<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoveryIndexAliasEntity;

interface DiscoveryIndexAliasRepositoryInterface
{
    public function save(DiscoveryIndexAliasEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryIndexAliasEntity $entity, bool $flush = false): void;
}

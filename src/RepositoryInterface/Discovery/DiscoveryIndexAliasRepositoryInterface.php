<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface\Discovery;

use App\Discovering\Entity\Discovery\DiscoveryIndexAliasEntity;

/**
 * Defines persistence operations for discovery index aliases.
 */
interface DiscoveryIndexAliasRepositoryInterface
{
    public function save(DiscoveryIndexAliasEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryIndexAliasEntity $entity, bool $flush = false): void;
}

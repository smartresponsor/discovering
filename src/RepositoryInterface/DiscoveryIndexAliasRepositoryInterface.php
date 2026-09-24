<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface;

use App\Discovering\Entity\DiscoveryIndexAliasEntity;

/**
 * Defines persistence operations for discovery index aliases.
 */
interface DiscoveryIndexAliasRepositoryInterface
{
    public function save(DiscoveryIndexAliasEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryIndexAliasEntity $entity, bool $flush = false): void;
}

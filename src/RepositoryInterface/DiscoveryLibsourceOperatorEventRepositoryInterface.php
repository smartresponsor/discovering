<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface;

use App\Discovering\Entity\DiscoveryLibsourceOperatorEventEntity;

/**
 * Defines persistence operations for libsource operator events.
 */
interface DiscoveryLibsourceOperatorEventRepositoryInterface
{
    public function save(DiscoveryLibsourceOperatorEventEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryLibsourceOperatorEventEntity $entity, bool $flush = false): void;
}

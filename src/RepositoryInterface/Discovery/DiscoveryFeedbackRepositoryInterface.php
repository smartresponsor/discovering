<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface\Discovery;

use App\Discovering\Entity\Discovery\DiscoveryFeedbackEntity;

/**
 * Defines persistence operations for discovery feedback records.
 */
interface DiscoveryFeedbackRepositoryInterface
{
    public function save(DiscoveryFeedbackEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryFeedbackEntity $entity, bool $flush = false): void;
}

<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface;

use App\Discovering\Entity\DiscoveryFeedbackEntity;

/**
 * Defines persistence operations for discovery feedback records.
 */
interface DiscoveryFeedbackRepositoryInterface
{
    public function save(DiscoveryFeedbackEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryFeedbackEntity $entity, bool $flush = false): void;
}

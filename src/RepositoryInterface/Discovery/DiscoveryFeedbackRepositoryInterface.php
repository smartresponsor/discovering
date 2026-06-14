<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoveryFeedbackEntity;

interface DiscoveryFeedbackRepositoryInterface
{
    public function save(DiscoveryFeedbackEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryFeedbackEntity $entity, bool $flush = false): void;
}

<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoveryRateLimitBucketEntity;

interface DiscoveryRateLimitBucketRepositoryInterface
{
    public function save(DiscoveryRateLimitBucketEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryRateLimitBucketEntity $entity, bool $flush = false): void;
}

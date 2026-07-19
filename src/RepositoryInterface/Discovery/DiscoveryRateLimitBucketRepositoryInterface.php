<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface\Discovery;

use App\Discovering\Entity\Discovery\DiscoveryRateLimitBucketEntity;

interface DiscoveryRateLimitBucketRepositoryInterface
{
    public function save(DiscoveryRateLimitBucketEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryRateLimitBucketEntity $entity, bool $flush = false): void;
}

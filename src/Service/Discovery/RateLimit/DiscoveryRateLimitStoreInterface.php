<?php

declare(strict_types=1);

namespace App\Service\Discovery\RateLimit;

interface DiscoveryRateLimitStoreInterface
{
    /**
     * @return array{count:int, resetAt:int}
     */
    public function increment(string $scope, string $actorKey, int $windowSeconds): array;
}

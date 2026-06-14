<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\RateLimit;

/**
 * Defines the contract for the discovery rate limit store capability within the discovery component.
 */
interface DiscoveryRateLimitStoreInterface
{
    /**
     * @return array{count:int, resetAt:int}
     */
    public function increment(string $scope, string $actorKey, int $windowSeconds): array;
}

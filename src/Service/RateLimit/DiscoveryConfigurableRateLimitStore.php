<?php

declare(strict_types=1);

namespace App\Discovering\Service\RateLimit;

use App\Discovering\Repository\RateLimit\DiscoveryDoctrineRateLimitStore;
use App\Discovering\ServiceInterface\RateLimit\DiscoveryRateLimitStoreInterface; /**
 * Provides the configurable discovery rate limit store capability within the discovery component.
 */
final class DiscoveryConfigurableRateLimitStore implements DiscoveryRateLimitStoreInterface
{
    public function __construct(
        private readonly DiscoveryFileRateLimitStore $fileStore,
        private readonly DiscoveryDoctrineRateLimitStore $doctrineStore,
        private readonly string $backend,
    ) {
    }

    /**
     * Performs the increment operation for this discovery service.
     */
    public function increment(string $scope, string $actorKey, int $windowSeconds): array
    {
        return $this->delegate()->increment($scope, $actorKey, $windowSeconds);
    }

    private function delegate(): DiscoveryRateLimitStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'doctrine' => $this->doctrineStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery rate limit backend "%s". Expected "file" or "doctrine".', $this->backend)),
        };
    }
}

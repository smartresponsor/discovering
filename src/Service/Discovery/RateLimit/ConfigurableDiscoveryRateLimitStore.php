<?php

declare(strict_types=1);

namespace App\Service\Discovery\RateLimit;

final class ConfigurableDiscoveryRateLimitStore implements DiscoveryRateLimitStoreInterface
{
    public function __construct(
        private readonly FileDiscoveryRateLimitStore $fileStore,
        private readonly PdoDiscoveryRateLimitStore $pdoStore,
        private readonly string $backend,
    ) {
    }

    public function increment(string $scope, string $actorKey, int $windowSeconds): array
    {
        return $this->delegate()->increment($scope, $actorKey, $windowSeconds);
    }

    private function delegate(): DiscoveryRateLimitStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'pdo' => $this->pdoStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery rate limit backend "%s". Expected "file" or "pdo".', $this->backend)),
        };
    }
}

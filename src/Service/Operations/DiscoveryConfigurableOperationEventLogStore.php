<?php

declare(strict_types=1);

namespace App\Discovering\Service\Operations;

use App\Discovering\DTO\DiscoveryOperationEventDTO;
use App\Discovering\Repository\Operations\DiscoveryDoctrineOperationEventLogStore;
use App\Discovering\ServiceInterface\Operations\DiscoveryOperationEventLogStoreInterface;

/**
 * Provides the configurable discovery operation event log store capability within the discovery component.
 */
final class DiscoveryConfigurableOperationEventLogStore implements DiscoveryOperationEventLogStoreInterface
{
    public function __construct(
        private readonly DiscoveryFileOperationEventLogStore $fileStore,
        private readonly DiscoveryDoctrineOperationEventLogStore $doctrineStore,
        private readonly string $backend,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryOperationEventDTO $event): void
    {
        $this->delegate()->append($event);
    }

    /**
     * Performs the all operation for this discovery service.
     */
    public function all(): array
    {
        return $this->delegate()->all();
    }

    /**
     * Performs the latest operation for this discovery service.
     */
    public function latest(int $limit = 25): array
    {
        return $this->delegate()->latest($limit);
    }

    /**
     * Performs the clear operation for this discovery service.
     */
    public function clear(): void
    {
        $this->delegate()->clear();
    }

    private function delegate(): DiscoveryOperationEventLogStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'doctrine' => $this->doctrineStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery operation log backend "%s". Expected "file" or "doctrine".', $this->backend)),
        };
    }
}

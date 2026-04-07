<?php

declare(strict_types=1);

namespace App\Service\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;


/**
 * Provides the configurable discovery operation event log store capability within the discovery component.
 */
final class ConfigurableDiscoveryOperationEventLogStore implements DiscoveryOperationEventLogStoreInterface
{
    public function __construct(
        private readonly FileDiscoveryOperationEventLogStore $fileStore,
        private readonly PdoDiscoveryOperationEventLogStore $pdoStore,
        private readonly string $backend,
        private readonly string $pdoDsn,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryOperationEvent $event): void
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
            'pdo' => trim($this->pdoDsn) !== '' ? $this->pdoStore : $this->fileStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery operation log backend "%s". Expected "file" or "pdo".', $this->backend)),
        };
    }
}

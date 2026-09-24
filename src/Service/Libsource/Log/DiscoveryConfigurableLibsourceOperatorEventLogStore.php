<?php

declare(strict_types=1);

namespace App\Discovering\Service\Libsource\Log;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\Repository\Libsource\DiscoveryDoctrineLibsourceOperatorEventLogStore;
use App\Discovering\ServiceInterface\Libsource\Log\DiscoveryLibsourceOperatorEventLogStoreInterface;

/**
 * Provides the configurable libsource operator event log store capability within the discovery component.
 */
final class DiscoveryConfigurableLibsourceOperatorEventLogStore implements DiscoveryLibsourceOperatorEventLogStoreInterface
{
    public function __construct(
        private readonly DiscoveryFileLibsourceOperatorEventLogStore $fileStore,
        private readonly DiscoveryDoctrineLibsourceOperatorEventLogStore $doctrineStore,
        private readonly string $backend,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryLibsourceOperatorEventDTO $event): void
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
     * Performs the clear operation for this discovery service.
     */
    public function clear(): void
    {
        $this->delegate()->clear();
    }

    private function delegate(): DiscoveryLibsourceOperatorEventLogStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'doctrine' => $this->doctrineStore,
            default => throw new \RuntimeException(sprintf('Unsupported libsource operator event log backend "%s". Expected "file" or "doctrine".', $this->backend)),
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;
use App\ServiceInterface\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;

/**
 * Provides the configurable libsource operator event log store capability within the discovery component.
 */
final class ConfigurableLibsourceOperatorEventLogStore implements LibsourceOperatorEventLogStoreInterface
{
    public function __construct(
        private readonly FileLibsourceOperatorEventLogStore $fileStore,
        private readonly DoctrineLibsourceOperatorEventLogStore $doctrineStore,
        private readonly string $backend,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(LibsourceOperatorEvent $event): void
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

    private function delegate(): LibsourceOperatorEventLogStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'doctrine' => $this->doctrineStore,
            default => throw new \RuntimeException(sprintf('Unsupported libsource operator event log backend "%s". Expected "file" or "doctrine".', $this->backend)),
        };
    }
}

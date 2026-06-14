<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryRebuildEvidenceStoreInterface;

/**
 * Provides the configurable discovery rebuild evidence store capability within the discovery component.
 */
final class ConfigurableDiscoveryRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    public function __construct(
        private readonly FileDiscoveryRebuildEvidenceStore $fileStore,
        private readonly DoctrineDiscoveryRebuildEvidenceStore $doctrineStore,
        private readonly string $backend,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryRebuildSummary $summary): void
    {
        $this->delegate()->append($summary);
    }

    /**
     * Performs the latest operation for this discovery service.
     */
    public function latest(int $limit = 20): array
    {
        return $this->delegate()->latest($limit);
    }

    private function delegate(): DiscoveryRebuildEvidenceStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'doctrine' => $this->doctrineStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery rebuild evidence backend "%s". Expected "file" or "doctrine".', $this->backend)),
        };
    }
}

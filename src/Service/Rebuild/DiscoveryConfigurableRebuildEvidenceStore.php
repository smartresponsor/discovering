<?php

declare(strict_types=1);

namespace App\Discovering\Service\Rebuild;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\Repository\Rebuild\DiscoveryDoctrineRebuildEvidenceStore;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryRebuildEvidenceStoreInterface;

/**
 * Provides the configurable discovery rebuild evidence store capability within the discovery component.
 */
final class DiscoveryConfigurableRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    public function __construct(
        private readonly DiscoveryFileRebuildEvidenceStore $fileStore,
        private readonly DiscoveryDoctrineRebuildEvidenceStore $doctrineStore,
        private readonly string $backend,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryRebuildSummaryDTO $summary): void
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

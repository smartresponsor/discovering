<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;

final class ConfigurableDiscoveryRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    public function __construct(
        private readonly FileDiscoveryRebuildEvidenceStore $fileStore,
        private readonly PdoDiscoveryRebuildEvidenceStore $pdoStore,
        private readonly string $backend,
        private readonly string $pdoDsn,
    ) {
    }

    public function append(DiscoveryRebuildSummary $summary): void
    {
        $this->delegate()->append($summary);
    }

    public function latest(int $limit = 20): array
    {
        return $this->delegate()->latest($limit);
    }

    private function delegate(): DiscoveryRebuildEvidenceStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'pdo' => trim($this->pdoDsn) !== '' ? $this->pdoStore : $this->fileStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery rebuild evidence backend "%s". Expected "file" or "pdo".', $this->backend)),
        };
    }
}

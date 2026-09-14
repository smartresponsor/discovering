<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Rebuild;

use App\Discovering\Dto\Discovery\DiscoveryRebuildSummary;
use App\Discovering\ServiceInterface\Discovery\Rebuild\DiscoveryRebuildEvidenceStoreInterface;

/**
 * Provides the file discovery rebuild evidence store capability within the discovery component.
 */
final class FileDiscoveryRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly DiscoveryRebuildEvidenceJsonSerializer $serializer,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryRebuildSummary $summary): void
    {
        $items = $this->readAll();
        $items[] = $summary;

        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0o777, true);
        }

        file_put_contents($this->path, $this->serializer->encode($items), LOCK_EX);
    }

    /**
     * Performs the latest operation for this discovery service.
     */
    public function latest(int $limit = 20): array
    {
        return array_slice(array_reverse($this->readAll()), 0, $limit);
    }

    /**
     * @return list<DiscoveryRebuildSummary>
     */
    private function readAll(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $payload = file_get_contents($this->path);
        if (!is_string($payload) || '' === $payload) {
            return [];
        }

        return $this->serializer->decode($payload);
    }
}

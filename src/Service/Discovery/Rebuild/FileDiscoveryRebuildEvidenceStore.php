<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;

final class FileDiscoveryRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly DiscoveryRebuildEvidenceJsonSerializer $serializer,
    ) {
    }

    public function append(DiscoveryRebuildSummary $summary): void
    {
        $items = $this->readAll();
        $items[] = $summary;

        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0o777, true);
        }

        file_put_contents($this->path, $this->serializer->encode($items));
    }

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
        if (!is_string($payload) || $payload === '') {
            return [];
        }

        return $this->serializer->decode($payload);
    }
}

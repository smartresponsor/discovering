<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryRebuildSummary
{
    /**
     * @param array<string, int> $indexedCountsByResource
     */
    public function __construct(
        public string $evidenceId,
        public string $resource,
        public string $rebuildMode,
        public string $backendName,
        public string $deploymentMode,
        public bool $zeroDowntimeReady,
        public string $startedAt,
        public string $finishedAt,
        public int $candidateDocumentCount,
        public int $indexedDocumentCount,
        public int $skippedDocumentCount,
        public array $indexedCountsByResource = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'evidenceId' => $this->evidenceId,
            'resource' => $this->resource,
            'rebuildMode' => $this->rebuildMode,
            'backendName' => $this->backendName,
            'deploymentMode' => $this->deploymentMode,
            'zeroDowntimeReady' => $this->zeroDowntimeReady,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt,
            'candidateDocumentCount' => $this->candidateDocumentCount,
            'indexedDocumentCount' => $this->indexedDocumentCount,
            'skippedDocumentCount' => $this->skippedDocumentCount,
            'indexedCountsByResource' => $this->indexedCountsByResource,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the discovery rollback execution result contract used by discovery application, management, or state coordination flows.
 */
final class DiscoveryRollbackExecutionResult
{
    /**
     * @param list<string> $notes
     */
    public function __construct(
        public bool $executed,
        public string $status,
        public string $backendName,
        public ?string $currentEvidenceId,
        public ?string $targetEvidenceId,
        public ?string $alias,
        public ?string $targetPhysicalIndex,
        public array $notes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'executed' => $this->executed,
            'status' => $this->status,
            'backendName' => $this->backendName,
            'currentEvidenceId' => $this->currentEvidenceId,
            'targetEvidenceId' => $this->targetEvidenceId,
            'alias' => $this->alias,
            'targetPhysicalIndex' => $this->targetPhysicalIndex,
            'notes' => $this->notes,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery rollback plan contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryRollbackPlanDTO
{
    /**
     * @param list<string> $notes
     */
    public function __construct(
        public bool $rollbackReady,
        public string $status,
        public ?string $currentEvidenceId,
        public ?string $previousEvidenceId,
        public ?string $currentPhysicalIndex,
        public ?string $rollbackTargetPhysicalIndex,
        public ?string $recommendedCommand,
        public array $notes = [],
    ) {
    }

    /**
     * Serializes this discovery value into its canonical transport array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rollbackReady' => $this->rollbackReady,
            'status' => $this->status,
            'currentEvidenceId' => $this->currentEvidenceId,
            'previousEvidenceId' => $this->previousEvidenceId,
            'currentPhysicalIndex' => $this->currentPhysicalIndex,
            'rollbackTargetPhysicalIndex' => $this->rollbackTargetPhysicalIndex,
            'recommendedCommand' => $this->recommendedCommand,
            'notes' => $this->notes,
        ];
    }
}

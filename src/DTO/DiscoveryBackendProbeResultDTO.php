<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery backend probe result contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryBackendProbeResultDTO
{
    /**
     * @param list<string> $details
     */
    public function __construct(
        public string $nameEntity,
        public string $backend,
        public string $target,
        public string $status,
        public array $details = [],
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
            'nameEntity' => $this->nameEntity,
            'backend' => $this->backend,
            'target' => $this->target,
            'status' => $this->status,
            'details' => $this->details,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Dto\Discovery;

/**
 * Represents the discovery backend probe result contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryBackendProbeResult
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

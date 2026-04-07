<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryBackendProbeResult
{
    /**
     * @param list<string> $details
     */
    public function __construct(
        public string $name,
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
            'name' => $this->name,
            'backend' => $this->backend,
            'target' => $this->target,
            'status' => $this->status,
            'details' => $this->details,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryBackendReachabilityReport
{
    /**
     * @param list<DiscoveryBackendProbeResult> $probes
     * @param list<string> $notes
     */
    public function __construct(
        public string $checkedAt,
        public int $performedProbeCount,
        public int $reachableProbeCount,
        public int $failingProbeCount,
        public int $skippedProbeCount,
        public array $probes = [],
        public array $notes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'checkedAt' => $this->checkedAt,
            'performedProbeCount' => $this->performedProbeCount,
            'reachableProbeCount' => $this->reachableProbeCount,
            'failingProbeCount' => $this->failingProbeCount,
            'skippedProbeCount' => $this->skippedProbeCount,
            'probes' => array_map(
                static fn (DiscoveryBackendProbeResult $probe): array => $probe->toArray(),
                $this->probes,
            ),
            'notes' => $this->notes,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery backend reachability report contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryBackendReachabilityReportDTO
{
    /**
     * @param list<DiscoveryBackendProbeResultDTO> $probes
     * @param list<string>                         $failingProbeNames
     * @param list<string>                         $notes
     */
    public function __construct(
        public string $checkedAt,
        public int $performedProbeCount,
        public int $reachableProbeCount,
        public int $failingProbeCount,
        public int $skippedProbeCount,
        public string $overallStatus,
        public string $recommendedAction,
        public array $probes = [],
        public array $failingProbeNames = [],
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
            'checkedAt' => $this->checkedAt,
            'performedProbeCount' => $this->performedProbeCount,
            'reachableProbeCount' => $this->reachableProbeCount,
            'failingProbeCount' => $this->failingProbeCount,
            'skippedProbeCount' => $this->skippedProbeCount,
            'overallStatus' => $this->overallStatus,
            'recommendedAction' => $this->recommendedAction,
            'probes' => array_map(
                static fn (DiscoveryBackendProbeResultDTO $probe): array => $probe->toArray(),
                $this->probes,
            ),
            'failingProbeNames' => $this->failingProbeNames,
            'notes' => $this->notes,
        ];
    }
}

<?php
declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the discovery result contract used by discovery application, management, or state coordination flows.
 */
final class DiscoveryResult
{
    /** @param list<DiscoveryHit> $hits */
    public function __construct(
        public DiscoveryQuery $query,
        public array $hits,
        public int $total,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'query' => [
                'query' => $this->query->query,
                'resource' => $this->query->resource,
                'limit' => $this->query->limit,
                'offset' => $this->query->offset,
                'filters' => $this->query->filters,
                'resourceWeights' => $this->query->resourceWeights,
                'mode' => $this->query->mode,
            ],
            'hits' => array_map(static fn (DiscoveryHit $hit): array => $hit->toArray(), $this->hits),
            'total' => $this->total,
        ];
    }
}

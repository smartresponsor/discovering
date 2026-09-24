<?php

declare(strict_types=1);

namespace App\Discovering\Service;

use App\Discovering\DTO\DiscoveryHitDTO;
use App\Discovering\DTO\DiscoveryQueryDTO;
use App\Discovering\DTO\DiscoveryResultDTO;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\DiscoveryServiceInterface;

/**
 * Provides the discovery capability within the discovery component.
 */
final class DiscoveryService implements DiscoveryServiceInterface
{
    public function __construct(
        private readonly DiscoveryBackendInterface $adapter,
        private readonly DiscoveryScoringService $scoringService,
        private readonly DiscoveryModePresetService $modePresetService,
    ) {
    }

    /**
     * Executes the discovery query workflow and returns the normalized result model.
     */
    public function discover(DiscoveryQueryDTO $query): DiscoveryResultDTO
    {
        $effectiveQuery = $this->modePresetService->apply($query);
        $resource = '' === $effectiveQuery->resource ? 'global' : $effectiveQuery->resource;
        $candidateLimit = max(50, $effectiveQuery->limit + $effectiveQuery->offset + 50);
        $rows = $this->adapter->search($resource, $effectiveQuery->query, $candidateLimit, 0);
        $hits = array_map(static fn (array $row): DiscoveryHitDTO => DiscoveryHitDTO::fromArray($row), $rows);
        $rankedHits = $this->scoringService->rank($hits, $effectiveQuery);
        $pagedHits = array_slice($rankedHits, $effectiveQuery->offset, $effectiveQuery->limit);

        return new DiscoveryResultDTO(query: $effectiveQuery, hits: $pagedHits, total: count($rankedHits));
    }
}

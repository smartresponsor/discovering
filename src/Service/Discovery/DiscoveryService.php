<?php
declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;


/**
 * Provides the discovery capability within the discovery component.
 */
final class DiscoveryService implements DiscoveryServiceInterface
{
    public function __construct(
        private readonly DiscoveryAdapterInterface $adapter,
        private readonly DiscoveryScoringService $scoringService,
        private readonly DiscoveryModePresetService $modePresetService,
    ) {
    }

    /**
     * Executes the discovery query workflow and returns the normalized result model.
     */
    public function discover(DiscoveryQuery $query): DiscoveryResult
    {
        $effectiveQuery = $this->modePresetService->apply($query);
        $resource = $effectiveQuery->resource === '' ? 'global' : $effectiveQuery->resource;
        $candidateLimit = max(50, $effectiveQuery->limit + $effectiveQuery->offset + 50);
        $rows = $this->adapter->search($resource, $effectiveQuery->query, $candidateLimit, 0);
        $hits = array_map(static fn (array $row): DiscoveryHit => DiscoveryHit::fromArray($row), $rows);
        $rankedHits = $this->scoringService->rank($hits, $effectiveQuery);
        $pagedHits = array_slice($rankedHits, $effectiveQuery->offset, $effectiveQuery->limit);

        return new DiscoveryResult(query: $effectiveQuery, hits: $pagedHits, total: count($rankedHits));
    }
}

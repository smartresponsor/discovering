<?php
declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;

final class DiscoveryService implements DiscoveryServiceInterface
{
    public function __construct(
        private readonly DiscoveryAdapterInterface $adapter,
        private readonly DiscoveryScoringService $scoringService,
    ) {
    }

    public function discover(DiscoveryQuery $query): DiscoveryResult
    {
        $resource = $query->resource === '' ? 'global' : $query->resource;
        $candidateLimit = max(50, $query->limit + $query->offset + 50);
        $rows = $this->adapter->search($resource, $query->query, $candidateLimit, 0);
        $hits = array_map(static fn (array $row): DiscoveryHit => DiscoveryHit::fromArray($row), $rows);
        $rankedHits = $this->scoringService->rank($hits, $query);
        $pagedHits = array_slice($rankedHits, $query->offset, $query->limit);

        return new DiscoveryResult(query: $query, hits: $pagedHits, total: count($rankedHits));
    }
}

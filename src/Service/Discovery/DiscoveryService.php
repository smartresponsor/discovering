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
    public function __construct(private readonly DiscoveryAdapterInterface $adapter)
    {
    }

    public function discover(DiscoveryQuery $query): DiscoveryResult
    {
        $resource = $query->resource === '' ? 'global' : $query->resource;
        $rows = $this->adapter->search($resource, $query->query, $query->limit, $query->offset);
        $hits = array_map(static fn (array $row): DiscoveryHit => DiscoveryHit::fromArray($row), $rows);

        return new DiscoveryResult(query: $query, hits: $hits, total: count($hits));
    }
}

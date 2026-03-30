<?php

declare(strict_types=1);

namespace App\Service\Discovery\Adapter;

use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;

final class SqliteFtsDiscoveryAdapter implements DiscoveryAdapterInterface
{
    public function discover(DiscoveryQuery $query): DiscoveryResult
    {
        return new DiscoveryResult(total: 0, hits: []);
    }

    public function rebuild(?string $resourceType = null): void
    {
    }
}

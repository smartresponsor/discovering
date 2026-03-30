<?php

declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;

final class DiscoveryService implements DiscoveryServiceInterface
{
    public function __construct(
        private readonly DiscoveryAdapterInterface $adapter,
    ) {
    }

    public function discover(DiscoveryQuery $query): DiscoveryResult
    {
        return $this->adapter->discover($query);
    }
}

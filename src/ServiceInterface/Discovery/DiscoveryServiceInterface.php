<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;

interface DiscoveryServiceInterface
{
    public function discover(DiscoveryQuery $query): DiscoveryResult;
}

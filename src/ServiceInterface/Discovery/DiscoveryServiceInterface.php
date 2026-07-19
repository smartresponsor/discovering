<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Discovery;

use App\Discovering\Dto\Discovery\DiscoveryQuery;
use App\Discovering\Dto\Discovery\DiscoveryResult;

/**
 * Defines the contract for the discovery service capability within the discovery component.
 */
interface DiscoveryServiceInterface
{
    /**
     * Performs the discover operation defined by this discovery contract.
     */
    public function discover(DiscoveryQuery $query): DiscoveryResult;
}

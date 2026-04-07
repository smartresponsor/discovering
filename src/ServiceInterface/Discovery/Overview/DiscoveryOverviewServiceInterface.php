<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Overview;

use App\Dto\Discovery\DiscoveryOverview;


/**
 * Defines the contract for the discovery overview service capability within the discovery component.
 */
interface DiscoveryOverviewServiceInterface
{
    /**
     * Performs the build overview operation defined by this discovery contract.
     */
    public function buildOverview(): DiscoveryOverview;
}

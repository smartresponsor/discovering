<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface;

use App\Discovering\DTO\DiscoveryQueryDTO;
use App\Discovering\DTO\DiscoveryResultDTO;

/**
 * Defines the contract for the discovery service capability within the discovery component.
 */
interface DiscoveryServiceInterface
{
    /**
     * Performs the discover operation defined by this discovery contract.
     */
    public function discover(DiscoveryQueryDTO $query): DiscoveryResultDTO;
}

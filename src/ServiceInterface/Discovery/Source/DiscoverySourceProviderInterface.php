<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Source;

use App\Dto\Discovery\DiscoverySourceRecord;


/**
 * Defines the contract for the discovery source provider capability within the discovery component.
 */
interface DiscoverySourceProviderInterface
{
    /**
     * Performs the get source name operation defined by this discovery contract.
     */
    public function getSourceName(): string;

    public function getResourceType(): string;

    /**
     * @return list<DiscoverySourceRecord>
     */
    public function provide(): array;
}

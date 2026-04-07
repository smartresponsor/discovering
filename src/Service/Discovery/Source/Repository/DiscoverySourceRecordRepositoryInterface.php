<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;


/**
 * Defines the contract for the discovery source record repository capability within the discovery component.
 */
interface DiscoverySourceRecordRepositoryInterface
{
    /**
     * Performs the get source name operation defined by this discovery contract.
     */
    public function getSourceName(): string;

    public function getResourceType(): string;

    /**
     * @return list<DiscoverySourceRecord>
     */
    public function all(): array;
}

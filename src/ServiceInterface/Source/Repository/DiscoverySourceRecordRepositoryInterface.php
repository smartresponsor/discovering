<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Source\Repository;

use App\Discovering\DTO\DiscoverySourceRecordDTO;

/**
 * Defines the contract for the discovery source record repository capability within the discovery component.
 */
interface DiscoverySourceRecordRepositoryInterface
{
    /**
     * Performs the get source nameEntity operation defined by this discovery contract.
     */
    public function getSourceName(): string;

    public function getResourceType(): string;

    /**
     * @return list<DiscoverySourceRecordDTO>
     */
    public function all(): array;
}

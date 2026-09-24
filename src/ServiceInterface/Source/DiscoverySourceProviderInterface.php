<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Source;

use App\Discovering\DTO\DiscoverySourceRecordDTO;

/**
 * Defines the contract for the discovery source provider capability within the discovery component.
 */
interface DiscoverySourceProviderInterface
{
    /**
     * Performs the get source nameEntity operation defined by this discovery contract.
     */
    public function getSourceName(): string;

    public function getResourceType(): string;

    /**
     * @return list<DiscoverySourceRecordDTO>
     */
    public function provide(): array;
}

<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Discovery\Rebuild;

/**
 * Defines the contract for the discovery staging capable adapter capability within the discovery component.
 */
interface DiscoveryStagingCapableAdapterInterface
{
    /**
     * Performs the supports staged rebuild operation defined by this discovery contract.
     */
    public function supportsStagedRebuild(): bool;
}

<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Rebuild;

/**
 * Defines the contract for the discovery staging capable backend capability within the discovery component.
 */
interface DiscoveryStagingCapableBackendInterface
{
    /**
     * Performs the supports staged rebuild operation defined by this discovery contract.
     */
    public function supportsStagedRebuild(): bool;
}

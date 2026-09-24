<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Support;

use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyManagementActionResultDTO;

/**
 * Defines the contract for the directory backed family management action service capability within the discovery component.
 */
interface DiscoveryDirectoryBackedFamilyManagementActionServiceInterface
{
    /**
     * Performs the audit registry operation defined by this discovery contract.
     */
    public function auditRegistry(): DiscoveryDirectoryBackedFamilyManagementActionResultDTO;

    public function ensureSampleRegistry(): DiscoveryDirectoryBackedFamilyManagementActionResultDTO;

    /**
     * Performs the migrate legacy storage operation defined by this discovery contract.
     */
    public function migrateLegacyStorage(): DiscoveryDirectoryBackedFamilyManagementActionResultDTO;
}

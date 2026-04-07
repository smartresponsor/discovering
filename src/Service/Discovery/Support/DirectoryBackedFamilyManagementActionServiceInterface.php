<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DirectoryBackedFamilyManagementActionResult;


/**
 * Defines the contract for the directory backed family management action service capability within the discovery component.
 */
interface DirectoryBackedFamilyManagementActionServiceInterface
{
    /**
     * Performs the audit registry operation defined by this discovery contract.
     */
    public function auditRegistry(): DirectoryBackedFamilyManagementActionResult;

    public function ensureSampleRegistry(): DirectoryBackedFamilyManagementActionResult;

    /**
     * Performs the migrate legacy storage operation defined by this discovery contract.
     */
    public function migrateLegacyStorage(): DirectoryBackedFamilyManagementActionResult;
}

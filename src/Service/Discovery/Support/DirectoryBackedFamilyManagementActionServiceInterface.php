<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DirectoryBackedFamilyManagementActionResult;

interface DirectoryBackedFamilyManagementActionServiceInterface
{
    public function auditRegistry(): DirectoryBackedFamilyManagementActionResult;

    public function ensureSampleRegistry(): DirectoryBackedFamilyManagementActionResult;

    public function migrateLegacyStorage(): DirectoryBackedFamilyManagementActionResult;
}

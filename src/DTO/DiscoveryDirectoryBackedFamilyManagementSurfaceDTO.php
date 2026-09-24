<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the directory backed family management surface contract used by discovery application, management, or state coordination flows.
 */
readonly class DiscoveryDirectoryBackedFamilyManagementSurfaceDTO
{
    /**
     * @param list<DiscoveryDirectoryBackedFamilyManagementEntryDTO>     $entries
     * @param list<DiscoveryDirectoryBackedFamilyManagementFileEntryDTO> $fileEntries
     */
    public function __construct(
        public string $sourceName,
        public string $storageDirectoryPath,
        public int $totalRecords,
        public int $totalFiles,
        public array $entries,
        public array $fileEntries,
    ) {
    }
}

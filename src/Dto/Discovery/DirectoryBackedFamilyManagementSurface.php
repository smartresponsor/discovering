<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

class DirectoryBackedFamilyManagementSurface
{
    /**
     * @param list<DirectoryBackedFamilyManagementEntry> $entries
     * @param list<DirectoryBackedFamilyManagementFileEntry> $fileEntries
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

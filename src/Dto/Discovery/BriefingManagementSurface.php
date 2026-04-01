<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class BriefingManagementSurface
{
    /**
     * @param list<BriefingManagementEntry> $entries
     * @param list<BriefingManagementFileEntry> $fileEntries
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

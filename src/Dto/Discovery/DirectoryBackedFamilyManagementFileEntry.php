<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

/**
 * Represents the directory backed family management file entry contract used by discovery application, management, or state coordination flows.
 */
readonly class DirectoryBackedFamilyManagementFileEntry
{
    public function __construct(
        public string $fileName,
        public string $path,
        public int $recordCount,
    ) {
    }
}

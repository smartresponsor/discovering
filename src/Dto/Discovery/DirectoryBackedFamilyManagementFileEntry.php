<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

class DirectoryBackedFamilyManagementFileEntry
{
    public function __construct(
        public string $fileName,
        public string $path,
        public int $recordCount,
    ) {
    }
}

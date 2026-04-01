<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class PlaybookManagementSurface
{
    /**
     * @param list<PlaybookManagementEntry> $entries
     */
    public function __construct(
        public string $sourceName,
        public string $storagePath,
        public int $totalRecords,
        public array $entries,
    ) {
    }
}

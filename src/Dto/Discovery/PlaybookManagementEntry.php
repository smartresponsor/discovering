<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class PlaybookManagementEntry
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $resourceId,
        public string $resourceType,
        public string $title,
        public string $status,
        public string $visibility,
        public array $tags,
    ) {
    }
}

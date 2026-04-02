<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

class DirectoryBackedFamilyManagementEntry
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

<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the directory backed family management entry contract used by discovery application, management, or state coordination flows.
 */
readonly class DiscoveryDirectoryBackedFamilyManagementEntryDTO
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

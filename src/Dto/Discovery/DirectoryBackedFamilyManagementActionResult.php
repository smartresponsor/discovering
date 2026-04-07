<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the directory backed family management action result contract used by discovery application, management, or state coordination flows.
 */
class DirectoryBackedFamilyManagementActionResult
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $actionName,
        public string $summary,
        public array $payload,
    ) {
    }
}

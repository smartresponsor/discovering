<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

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

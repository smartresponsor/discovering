<?php

declare(strict_types=1);

namespace App\Discovering\Dto\Discovery;

/**
 * Represents the libsource management action result contract used by discovery application, management, or state coordination flows.
 */
final readonly class LibsourceManagementActionResult
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

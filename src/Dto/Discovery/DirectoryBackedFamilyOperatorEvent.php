<?php

declare(strict_types=1);

namespace App\Discovering\Dto\Discovery;

/**
 * Represents the directory backed family operator event contract used by discovery application, management, or state coordination flows.
 */
readonly class DirectoryBackedFamilyOperatorEvent
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $eventName,
        public string $level,
        public string $summary,
        public array $context = [],
    ) {
    }
}

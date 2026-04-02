<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

class DirectoryBackedFamilyOperatorEvent
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

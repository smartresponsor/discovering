<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the libsource operator event contract used by discovery application, management, or state coordination flows.
 */
final class LibsourceOperatorEvent
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

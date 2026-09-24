<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the libsource event log query contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryLibsourceEventLogQueryDTO
{
    public function __construct(
        public ?string $preset = null,
        public ?string $search = null,
        public ?string $level = null,
        public int $page = 1,
        public int $perPage = 10,
    ) {
    }
}

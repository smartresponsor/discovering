<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the reindex request contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryReindexRequestDTO
{
    /** @param array<int, string> $ids */
    public function __construct(
        public string $resource = 'global',
        public string $rebuildMode = 'full',
        public array $ids = [],
        public string $deploymentMode = 'auto',
    ) {
    }
}

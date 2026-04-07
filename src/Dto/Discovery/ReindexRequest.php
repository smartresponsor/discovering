<?php
declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the reindex request contract used by discovery application, management, or state coordination flows.
 */
final class ReindexRequest
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

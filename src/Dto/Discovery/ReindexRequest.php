<?php
declare(strict_types=1);

namespace App\Dto\Discovery;

final class ReindexRequest
{
    /** @param array<int, string> $ids */
    public function __construct(
        public string $resource = 'global',
        public string $rebuildMode = 'full',
        public array $ids = [],
    ) {
    }
}

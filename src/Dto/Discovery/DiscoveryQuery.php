<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryQuery
{
    public function __construct(
        public ?string $term = null,
        public ?string $resourceType = null,
        public array $filters = [],
        public int $limit = 25,
        public int $offset = 0,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\ValueObject\Discovery;

final class DiscoveryScope
{
    public function __construct(
        public ?string $resourceType = null,
        public array $filters = [],
    ) {
    }
}

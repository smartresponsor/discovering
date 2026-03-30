<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryHit
{
    public function __construct(
        public string $resourceType,
        public string $resourceId,
        public string $title,
        public ?string $snippet = null,
        public float $score = 0.0,
        public array $metadata = [],
    ) {
    }
}

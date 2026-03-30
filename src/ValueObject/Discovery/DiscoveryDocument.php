<?php

declare(strict_types=1);

namespace App\ValueObject\Discovery;

final class DiscoveryDocument
{
    public function __construct(
        public string $resourceType,
        public string $resourceId,
        public string $title,
        public string $body,
        public array $filters = [],
        public array $metadata = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoverySourceRecord
{
    /**
     * @param array<string, scalar|array|null> $filters
     * @param array<string, mixed> $metadata
     */
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

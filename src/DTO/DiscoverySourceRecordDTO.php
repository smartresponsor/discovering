<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery source record contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoverySourceRecordDTO
{
    /**
     * @param array<string, mixed> $filters
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

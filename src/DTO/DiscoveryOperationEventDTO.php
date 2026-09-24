<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery operation event contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryOperationEventDTO
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $requestId,
        public string $channel,
        public string $operation,
        public string $status,
        public string $occurredAt,
        public array $context = [],
    ) {
    }
}

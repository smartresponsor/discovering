<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryOperationEvent
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

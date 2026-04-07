<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the discovery rate limit decision contract used by discovery application, management, or state coordination flows.
 */
final class DiscoveryRateLimitDecision
{
    public function __construct(
        public string $scope,
        public int $limit,
        public int $remaining,
        public int $resetAt,
        public int $retryAfterSeconds,
        public bool $exceeded,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'scope' => $this->scope,
            'limit' => $this->limit,
            'remaining' => $this->remaining,
            'resetAt' => $this->resetAt,
            'retryAfterSeconds' => $this->retryAfterSeconds,
            'exceeded' => $this->exceeded,
        ];
    }
}

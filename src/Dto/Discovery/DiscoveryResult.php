<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryResult
{
    /**
     * @param list<DiscoveryHit> $hits
     */
    public function __construct(
        public int $total,
        public array $hits,
    ) {
    }
}

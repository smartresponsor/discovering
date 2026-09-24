<?php

declare(strict_types=1);

namespace App\Discovering\ValueObject;

/**
 * Represents the discovery scope value within the discovery domain and runtime contracts.
 */
final readonly class DiscoveryScope
{
    public function __construct(
        public string $resource,
        public bool $global = false,
    ) {
    }

    public static function global(): self
    {
        return new self(resource: 'global', global: true);
    }
}

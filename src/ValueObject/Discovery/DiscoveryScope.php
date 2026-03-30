<?php
declare(strict_types=1);

namespace App\ValueObject\Discovery;

final class DiscoveryScope
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

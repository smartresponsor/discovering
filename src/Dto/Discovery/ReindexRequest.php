<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class ReindexRequest
{
    public function __construct(
        public ?string $resourceType = null,
        public ?string $resourceId = null,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class LibsourceEventLogQuery
{
    public function __construct(
        public ?string $preset = null,
        public ?string $search = null,
        public ?string $level = null,
        public int $page = 1,
        public int $perPage = 10,
    ) {
    }
}

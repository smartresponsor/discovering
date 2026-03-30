<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryQuery
{
    public function __construct(
        public ?string $term = null,
        public ?string $resourceType = null,
        public ?string $status = null,
        public ?string $visibility = null,
        public array $filters = [],
        public int $limit = 25,
        public int $offset = 0,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toFilterMap(): array
    {
        $filters = [];

        foreach ($this->filters as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $filters[(string) $name] = (string) $value;
        }

        if ($this->status !== null && $this->status !== '') {
            $filters['status'] = $this->status;
        }

        if ($this->visibility !== null && $this->visibility !== '') {
            $filters['visibility'] = $this->visibility;
        }

        return $filters;
    }
}

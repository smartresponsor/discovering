<?php

declare(strict_types=1);

namespace App\Discovering\ValueObject;

/**
 * Represents the discovery document value within the discovery domain and runtime contracts.
 */
final readonly class DiscoveryDocument
{
    /** @param array<string, scalar|null> $fields */
    public function __construct(
        public string $id,
        public string $resource,
        public string $title,
        public string $reference = '',
        public string $status = '',
        public string $content = '',
        public array $fields = [],
    ) {
    }

    /** @return array<string, scalar|null> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'resource' => $this->resource,
            'title' => $this->title,
            'reference' => $this->reference,
            'status' => $this->status,
            'content' => $this->content,
        ] + $this->fields;
    }
}

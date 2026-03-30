<?php
declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryHit
{
    public function __construct(
        public string $id,
        public string $title,
        public string $resource,
        public string $reference = '',
        public string $status = '',
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            id: (string) ($payload['id'] ?? ''),
            title: (string) ($payload['title'] ?? $payload['name'] ?? ''),
            resource: (string) ($payload['resource'] ?? 'global'),
            reference: (string) ($payload['reference'] ?? $payload['metaCode'] ?? ''),
            status: (string) ($payload['status'] ?? ''),
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'resource' => $this->resource,
            'reference' => $this->reference,
            'status' => $this->status,
        ];
    }
}

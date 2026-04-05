<?php
declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Adapter;

interface DiscoveryAdapterInterface
{
    /** @param array<string, mixed> $document */
    public function upsert(string $resource, string $id, array $document): void;
    public function remove(string $resource, string $id): void;
    /** @return array<int, array<string, mixed>> */
    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array;
    public function createIndex(string $resource): void;
    public function swapAlias(string $from, string $to): void;
    public function getBackendName(): string;
}

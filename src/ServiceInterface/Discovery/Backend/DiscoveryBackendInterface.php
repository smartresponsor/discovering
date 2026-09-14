<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Discovery\Backend;

/**
 * Defines the contract for the discovery backend capability within the discovery component.
 */
interface DiscoveryBackendInterface
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

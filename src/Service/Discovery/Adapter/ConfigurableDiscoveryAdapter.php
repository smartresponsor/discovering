<?php

declare(strict_types=1);

namespace App\Service\Discovery\Adapter;

use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableAdapterInterface;

final class ConfigurableDiscoveryAdapter implements DiscoveryAdapterInterface, DiscoveryStagingCapableAdapterInterface
{
    public function __construct(
        private readonly string $backend,
        private readonly string $meiliBase,
        private readonly SqliteFtsDiscoveryAdapter $sqliteAdapter,
        private readonly MeiliDiscoveryAdapter $meiliAdapter,
    ) {
    }

    public function upsert(string $resource, string $id, array $document): void
    {
        $this->active()->upsert($resource, $id, $document);
    }

    public function remove(string $resource, string $id): void
    {
        $this->active()->remove($resource, $id);
    }

    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        return $this->active()->search($resource, $query, $limit, $offset);
    }

    public function createIndex(string $resource): void
    {
        $this->active()->createIndex($resource);
    }

    public function swapAlias(string $from, string $to): void
    {
        $this->active()->swapAlias($from, $to);
    }

    public function getBackendName(): string
    {
        return $this->active()->getBackendName();
    }

    public function supportsStagedRebuild(): bool
    {
        $active = $this->active();

        return $active instanceof DiscoveryStagingCapableAdapterInterface
            && $active->supportsStagedRebuild();
    }

    private function active(): DiscoveryAdapterInterface
    {
        if (strtolower(trim($this->backend)) === 'meili' && trim($this->meiliBase) !== '') {
            return $this->meiliAdapter;
        }

        return $this->sqliteAdapter;
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Service\Backend;

use App\Discovering\Repository\Backend\DiscoverySqliteFtsBackend;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryStagingCapableBackendInterface;

/**
 * Selects the configured discovery backend used by the discovery runtime.
 */
final class DiscoveryConfigurableBackend implements DiscoveryBackendInterface, DiscoveryStagingCapableBackendInterface
{
    public function __construct(
        private readonly string $backend,
        private readonly string $meiliBase,
        private readonly DiscoverySqliteFtsBackend $sqliteBackend,
        private readonly DiscoveryMeiliBackend $meiliBackend,
    ) {
    }

    /**
     * Performs the upsert operation for this discovery service.
     */
    public function upsert(string $resource, string $id, array $document): void
    {
        $this->active()->upsert($resource, $id, $document);
    }

    /**
     * Performs the remove operation for this discovery service.
     */
    public function remove(string $resource, string $id): void
    {
        $this->active()->remove($resource, $id);
    }

    /**
     * Executes the search workflow against the active discovery source or backend.
     */
    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        return $this->active()->search($resource, $query, $limit, $offset);
    }

    /**
     * Performs the create index operation for this discovery service.
     */
    public function createIndex(string $resource): void
    {
        $this->active()->createIndex($resource);
    }

    /**
     * Performs the swap alias operation for this discovery service.
     */
    public function swapAlias(string $from, string $to): void
    {
        $this->active()->swapAlias($from, $to);
    }

    /**
     * Returns the backend nameEntity value exposed by this service.
     */
    public function getBackendName(): string
    {
        return $this->active()->getBackendName();
    }

    /**
     * Performs the supports staged rebuild operation for this discovery service.
     */
    public function supportsStagedRebuild(): bool
    {
        $active = $this->active();

        return $active instanceof DiscoveryStagingCapableBackendInterface
            && $active->supportsStagedRebuild();
    }

    private function active(): DiscoveryBackendInterface
    {
        if ('meili' === strtolower(trim($this->backend)) && '' !== trim($this->meiliBase)) {
            return $this->meiliBackend;
        }

        return $this->sqliteBackend;
    }
}

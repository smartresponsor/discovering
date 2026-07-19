<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Topology;

use App\Discovering\Dto\Discovery\DiscoveryStateStoreDescriptor;
use App\Discovering\Dto\Discovery\DiscoveryStateTopology;

/**
 * Builds the discovery state topology output used by discovery management or diagnostics flows.
 */
final class DiscoveryStateTopologyBuilder
{
    public function __construct(
        private readonly string $projectDir,
        private readonly string $discoverySqlitePath,
        private readonly string $indexBackend,
        private readonly string $meiliUrl,
        private readonly string $meiliIndexPrefix,
        private readonly string $operationLogPath,
        private readonly string $operationLogBackend,
        private readonly string $rebuildEvidencePath,
        private readonly string $rebuildEvidenceBackend,
        private readonly string $libsourceEventLogPath,
        private readonly string $libsourceEventLogBackend,
        private readonly string $rateLimitStorePath,
        private readonly string $rateLimitBackend,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryStateTopology
    {
        $localStateRoot = $this->normalizePath($this->projectDir.'/var/discovery');

        $stores = [
            $this->discoveryIndexStore($localStateRoot),
            $this->feedbackStore(),
            $this->coordinationStore('operationLog', $this->operationLogPath, $this->operationLogBackend, $localStateRoot, 'shared-event-history'),
            $this->coordinationStore('rebuildEvidence', $this->rebuildEvidencePath, $this->rebuildEvidenceBackend, $localStateRoot, 'shared-evidence-history'),
            $this->coordinationStore('libsourceEventLog', $this->libsourceEventLogPath, $this->libsourceEventLogBackend, $localStateRoot, 'shared-operator-history'),
            $this->coordinationStore('rateLimitStore', $this->rateLimitStorePath, $this->rateLimitBackend, $localStateRoot, 'shared-counter-coordination'),
        ];

        $sharedStateConfigured = false;
        $distributedReady = true;
        $notes = [];

        foreach ($stores as $store) {
            if ($store->sharedConfigured) {
                $sharedStateConfigured = true;
            }

            if (!$store->multiReplicaWriteReady) {
                $distributedReady = false;
            }
        }

        if (!$sharedStateConfigured) {
            $notes[] = 'All discovery state paths still resolve under the local var/discovery root.';
        } else {
            $notes[] = 'One or more discovery state stores are configured outside the local var/discovery root or through a shared coordination backend.';
        }

        if ('meili' === strtolower(trim($this->indexBackend)) && '' !== trim($this->meiliUrl)) {
            $notes[] = 'Discovery index uses a shared Meilisearch backend.';
        } else {
            $notes[] = 'Discovery index remains local SQLite FTS until Meilisearch is configured.';
        }

        $sharedDoctrineStores = [];
        foreach ([
            ['nameEntity' => 'operation log', 'backend' => $this->operationLogBackend],
            ['nameEntity' => 'rebuild evidence', 'backend' => $this->rebuildEvidenceBackend],
            ['nameEntity' => 'libsource event log', 'backend' => $this->libsourceEventLogBackend],
            ['nameEntity' => 'rate limiting', 'backend' => $this->rateLimitBackend],
        ] as $candidate) {
            if ('doctrine' === strtolower(trim((string) $candidate['backend']))) {
                $sharedDoctrineStores[] = sprintf('%s uses a shared Doctrine ORM coordination backend.', ucfirst((string) $candidate['nameEntity']));
            }
        }

        if ([] === $sharedDoctrineStores) {
            $notes[] = 'Operation, evidence, libsource event, and rate-limit stores remain file-oriented unless their Doctrine backends are configured.';
        } else {
            foreach ($sharedDoctrineStores as $note) {
                $notes[] = $note;
            }
        }

        if ($distributedReady) {
            $notes[] = 'Overall distributed readiness can be treated as true when the mutable discovery stores are backed by Doctrine or another shared backend.';
        } else {
            $notes[] = 'Overall distributed readiness still remains false until every mutable discovery store moves beyond local file-backed state.';
        }

        return new DiscoveryStateTopology(
            localStateRoot: $localStateRoot,
            sharedStateConfigured: $sharedStateConfigured,
            distributedReady: $distributedReady,
            stores: $stores,
            notes: $notes,
        );
    }

    private function discoveryIndexStore(string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        if ('meili' === strtolower(trim($this->indexBackend))) {
            $sharedConfigured = '' !== trim($this->meiliUrl);
            $prefix = trim($this->meiliIndexPrefix);
            $path = '' === trim($this->meiliUrl)
                ? sprintf('meili:%s', '' === $prefix ? 'discovering' : $prefix)
                : sprintf('%s#%s', trim($this->meiliUrl), '' === $prefix ? 'discovering' : $prefix);

            return new DiscoveryStateStoreDescriptor(
                name: 'discoveryIndex',
                backend: 'meilisearch',
                path: $path,
                storageMode: 'service',
                sharedConfigured: $sharedConfigured,
                multiReplicaWriteReady: $sharedConfigured,
                concerns: ['search-service-coordination', 'eventual-index-convergence'],
            );
        }

        return $this->sqliteStore('discoveryIndex', $this->discoverySqlitePath, $localStateRoot);
    }

    private function feedbackStore(): DiscoveryStateStoreDescriptor
    {
        return new DiscoveryStateStoreDescriptor(
            name: 'feedbackStore',
            backend: 'doctrine',
            path: 'doctrine:discovery_feedback',
            storageMode: 'database',
            sharedConfigured: true,
            multiReplicaWriteReady: true,
            concerns: ['database-coordination-store', 'shared-feedback-coordination'],
        );
    }

    private function sqliteStore(string $nameEntity, string $path, string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        $normalizedPath = $this->normalizePath($path);
        $sharedConfigured = !$this->isLocalStatePath($normalizedPath, $localStateRoot);

        $concerns = ['sqlite-single-writer'];
        if ($sharedConfigured) {
            $concerns[] = 'shared-filesystem-locking-risk';
        }

        return new DiscoveryStateStoreDescriptor(
            name: $nameEntity,
            backend: 'sqlite',
            path: $normalizedPath,
            storageMode: $sharedConfigured ? 'shared_file' : 'local_file',
            sharedConfigured: $sharedConfigured,
            multiReplicaWriteReady: false,
            concerns: $concerns,
        );
    }

    private function coordinationStore(
        string $nameEntity,
        string $path,
        string $backend,
        string $localStateRoot,
        string $coordinationConcern,
    ): DiscoveryStateStoreDescriptor {
        if ('doctrine' === strtolower(trim($backend))) {
            return new DiscoveryStateStoreDescriptor(
                name: $nameEntity,
                backend: 'doctrine',
                path: sprintf('doctrine:%s', $nameEntity),
                storageMode: 'database',
                sharedConfigured: true,
                multiReplicaWriteReady: true,
                concerns: ['database-coordination-store', $coordinationConcern],
            );
        }

        return $this->jsonStore($nameEntity, $path, $localStateRoot);
    }

    private function jsonStore(string $nameEntity, string $path, string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        $normalizedPath = $this->normalizePath($path);
        $sharedConfigured = !$this->isLocalStatePath($normalizedPath, $localStateRoot);

        $concerns = ['json-read-modify-write'];
        if ($sharedConfigured) {
            $concerns[] = 'shared-filesystem-append-risk';
        }

        return new DiscoveryStateStoreDescriptor(
            name: $nameEntity,
            backend: 'json_file',
            path: $normalizedPath,
            storageMode: $sharedConfigured ? 'shared_file' : 'local_file',
            sharedConfigured: $sharedConfigured,
            multiReplicaWriteReady: false,
            concerns: $concerns,
        );
    }

    private function isLocalStatePath(string $path, string $localStateRoot): bool
    {
        return $path === $localStateRoot || str_starts_with($path, $localStateRoot.'/');
    }

    private function normalizePath(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));

        return rtrim($normalized, '/');
    }
}

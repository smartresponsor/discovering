<?php

declare(strict_types=1);

namespace App\Service\Discovery\Topology;

use App\Dto\Discovery\DiscoveryStateStoreDescriptor;
use App\Dto\Discovery\DiscoveryStateTopology;

final class DiscoveryStateTopologyBuilder
{
    public function __construct(
        private readonly string $projectDir,
        private readonly string $discoverySqlitePath,
        private readonly string $feedbackSqlitePath,
        private readonly string $operationLogPath,
        private readonly string $rebuildEvidencePath,
        private readonly string $libsourceEventLogPath,
        private readonly string $rateLimitBackend,
        private readonly string $rateLimitStorePath,
        private readonly string $rateLimitPdoDsn,
        private readonly string $rateLimitPdoTable,
    ) {
    }

    public function build(): DiscoveryStateTopology
    {
        $localStateRoot = $this->normalizePath($this->projectDir . '/var/discovery');

        $stores = [
            $this->sqliteStore('discoveryIndex', $this->discoverySqlitePath, $localStateRoot),
            $this->sqliteStore('feedbackStore', $this->feedbackSqlitePath, $localStateRoot),
            $this->jsonStore('operationLog', $this->operationLogPath, $localStateRoot),
            $this->jsonStore('rebuildEvidence', $this->rebuildEvidencePath, $localStateRoot),
            $this->jsonStore('libsourceEventLog', $this->libsourceEventLogPath, $localStateRoot),
            $this->rateLimitStore($localStateRoot),
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

        $notes[] = 'SQLite-backed discovery index and feedback state remain single-node oriented and are not treated as multi-replica write-safe.';
        $notes[] = 'JSON file-backed operation, evidence, and libsource event stores use local file mutation semantics and are not treated as distributed coordination stores.';

        if (strtolower(trim($this->rateLimitBackend)) === 'pdo' && trim($this->rateLimitPdoDsn) !== '') {
            $notes[] = 'Rate limiting can now use a shared PDO coordination backend, but overall distributed readiness still remains false until other mutable discovery stores move beyond SQLite and JSON files.';
        } else {
            $notes[] = 'Rate limiting still defaults to a JSON file store unless a stronger PDO coordination backend is configured.';
        }

        return new DiscoveryStateTopology(
            localStateRoot: $localStateRoot,
            sharedStateConfigured: $sharedStateConfigured,
            distributedReady: $distributedReady,
            stores: $stores,
            notes: $notes,
        );
    }

    private function sqliteStore(string $name, string $path, string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        $normalizedPath = $this->normalizePath($path);
        $sharedConfigured = !$this->isLocalStatePath($normalizedPath, $localStateRoot);

        $concerns = ['sqlite-single-writer'];
        if ($sharedConfigured) {
            $concerns[] = 'shared-filesystem-locking-risk';
        }

        return new DiscoveryStateStoreDescriptor(
            name: $name,
            backend: 'sqlite',
            path: $normalizedPath,
            storageMode: $sharedConfigured ? 'shared_file' : 'local_file',
            sharedConfigured: $sharedConfigured,
            multiReplicaWriteReady: false,
            concerns: $concerns,
        );
    }

    private function jsonStore(string $name, string $path, string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        $normalizedPath = $this->normalizePath($path);
        $sharedConfigured = !$this->isLocalStatePath($normalizedPath, $localStateRoot);

        $concerns = ['json-read-modify-write'];
        if ($sharedConfigured) {
            $concerns[] = 'shared-filesystem-append-risk';
        }

        return new DiscoveryStateStoreDescriptor(
            name: $name,
            backend: 'json_file',
            path: $normalizedPath,
            storageMode: $sharedConfigured ? 'shared_file' : 'local_file',
            sharedConfigured: $sharedConfigured,
            multiReplicaWriteReady: false,
            concerns: $concerns,
        );
    }

    private function rateLimitStore(string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        $backend = strtolower(trim($this->rateLimitBackend));
        if ($backend === 'pdo') {
            $dsn = trim($this->rateLimitPdoDsn);
            $sharedConfigured = $dsn !== '';
            $sqliteDsn = str_starts_with(strtolower($dsn), 'sqlite:');

            $concerns = ['database-coordination-store'];
            if ($sqliteDsn) {
                $concerns[] = 'sqlite-single-writer';
            } else {
                $concerns[] = 'shared-counter-coordination';
            }

            return new DiscoveryStateStoreDescriptor(
                name: 'rateLimitStore',
                backend: 'pdo_table',
                path: $dsn === '' ? sprintf('pdo:%s', $this->rateLimitPdoTable) : sprintf('%s#%s', $dsn, $this->rateLimitPdoTable),
                storageMode: 'database',
                sharedConfigured: $sharedConfigured,
                multiReplicaWriteReady: $sharedConfigured && !$sqliteDsn,
                concerns: $concerns,
            );
        }

        return $this->jsonStore('rateLimitStore', $this->rateLimitStorePath, $localStateRoot);
    }

    private function isLocalStatePath(string $path, string $localStateRoot): bool
    {
        return $path === $localStateRoot || str_starts_with($path, $localStateRoot . '/');
    }

    private function normalizePath(string $path): string
    {
        $normalized = str_replace('\\', '/', trim($path));

        return rtrim($normalized, '/');
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Discovery\Topology;

use App\Dto\Discovery\DiscoveryStateStoreDescriptor;
use App\Dto\Discovery\DiscoveryStateTopology;


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
        private readonly string $feedbackPath,
        private readonly string $feedbackBackend,
        private readonly string $feedbackPdoDsn,
        private readonly string $feedbackPdoTable,
        private readonly string $operationLogPath,
        private readonly string $operationLogBackend,
        private readonly string $operationLogPdoDsn,
        private readonly string $operationLogPdoTable,
        private readonly string $rebuildEvidencePath,
        private readonly string $rebuildEvidenceBackend,
        private readonly string $rebuildEvidencePdoDsn,
        private readonly string $rebuildEvidencePdoTable,
        private readonly string $libsourceEventLogPath,
        private readonly string $libsourceEventLogBackend,
        private readonly string $libsourceEventLogPdoDsn,
        private readonly string $libsourceEventLogPdoTable,
        private readonly string $rateLimitBackend,
        private readonly string $rateLimitStorePath,
        private readonly string $rateLimitPdoDsn,
        private readonly string $rateLimitPdoTable,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryStateTopology
    {
        $localStateRoot = $this->normalizePath($this->projectDir . '/var/discovery');

        $stores = [
            $this->discoveryIndexStore($localStateRoot),
            $this->feedbackStore($localStateRoot),
            $this->coordinationStore('operationLog', $this->operationLogPath, $this->operationLogBackend, $this->operationLogPdoDsn, $this->operationLogPdoTable, $localStateRoot, 'shared-event-history'),
            $this->coordinationStore('rebuildEvidence', $this->rebuildEvidencePath, $this->rebuildEvidenceBackend, $this->rebuildEvidencePdoDsn, $this->rebuildEvidencePdoTable, $localStateRoot, 'shared-evidence-history'),
            $this->coordinationStore('libsourceEventLog', $this->libsourceEventLogPath, $this->libsourceEventLogBackend, $this->libsourceEventLogPdoDsn, $this->libsourceEventLogPdoTable, $localStateRoot, 'shared-operator-history'),
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

        $indexUsesMeili = strtolower(trim($this->indexBackend)) === 'meili' && trim($this->meiliUrl) !== '';
        $feedbackUsesPdo = strtolower(trim($this->feedbackBackend)) === 'pdo' && trim($this->feedbackPdoDsn) !== '';
        if ($indexUsesMeili && $feedbackUsesPdo) {
            $notes[] = 'Discovery index uses a shared Meilisearch backend.';
            $notes[] = 'Feedback learning uses a shared PDO coordination backend.';
        } elseif ($indexUsesMeili) {
            $notes[] = 'Discovery index uses a shared Meilisearch backend.';
            $notes[] = 'Feedback learning still remains SQLite-oriented until a shared PDO backend is configured.';
        } elseif ($feedbackUsesPdo) {
            $notes[] = 'Feedback learning uses a shared PDO coordination backend.';
            $notes[] = 'SQLite-backed discovery index state still remains single-node oriented and is not treated as multi-replica write-safe.';
        } else {
            $notes[] = 'SQLite-backed discovery index and feedback state remain single-node oriented and are not treated as multi-replica write-safe.';
        }

        $strongerStoreNotes = [];
        foreach ([
            ['name' => 'operation log', 'backend' => $this->operationLogBackend, 'dsn' => $this->operationLogPdoDsn],
            ['name' => 'rebuild evidence', 'backend' => $this->rebuildEvidenceBackend, 'dsn' => $this->rebuildEvidencePdoDsn],
            ['name' => 'libsource event log', 'backend' => $this->libsourceEventLogBackend, 'dsn' => $this->libsourceEventLogPdoDsn],
            ['name' => 'rate limiting', 'backend' => $this->rateLimitBackend, 'dsn' => $this->rateLimitPdoDsn],
        ] as $candidate) {
            if (strtolower(trim((string) $candidate['backend'])) === 'pdo' && trim((string) $candidate['dsn']) !== '') {
                $strongerStoreNotes[] = sprintf('%s uses a shared PDO coordination backend.', ucfirst((string) $candidate['name']));
            }
        }

        if ($strongerStoreNotes === []) {
            $notes[] = 'Operation, evidence, libsource event, and rate-limit stores still default to local JSON files unless their stronger PDO coordination backends are configured.';
        } else {
            foreach ($strongerStoreNotes as $note) {
                $notes[] = $note;
            }
        }

        if ($indexUsesMeili && $feedbackUsesPdo) {
            $notes[] = 'Overall distributed readiness can be treated as true when stronger coordination stores are configured for the remaining mutable discovery state.';
        } elseif ($indexUsesMeili) {
            $notes[] = 'Overall distributed readiness still remains false until feedback state moves beyond SQLite single-node storage.';
        } elseif ($feedbackUsesPdo) {
            $notes[] = 'Overall distributed readiness still remains false until discovery index moves beyond SQLite single-node storage.';
        } elseif ($strongerStoreNotes !== []) {
            $notes[] = 'Overall distributed readiness still remains false until discovery index and feedback state move beyond SQLite single-node storage.';
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
        if (strtolower(trim($this->indexBackend)) === 'meili') {
            $sharedConfigured = trim($this->meiliUrl) !== '';
            $prefix = trim($this->meiliIndexPrefix);
            $path = trim($this->meiliUrl) === ''
                ? sprintf('meili:%s', $prefix === '' ? 'discovering' : $prefix)
                : sprintf('%s#%s', trim($this->meiliUrl), $prefix === '' ? 'discovering' : $prefix);

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

    private function feedbackStore(string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        if (strtolower(trim($this->feedbackBackend)) === 'pdo') {
            return $this->coordinationStore(
                name: 'feedbackStore',
                path: $this->feedbackPath,
                backend: $this->feedbackBackend,
                pdoDsn: $this->feedbackPdoDsn,
                pdoTable: $this->feedbackPdoTable,
                localStateRoot: $localStateRoot,
                coordinationConcern: 'shared-feedback-coordination',
            );
        }

        return $this->sqliteStore('feedbackStore', $this->feedbackPath, $localStateRoot);
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

    private function coordinationStore(
        string $name,
        string $path,
        string $backend,
        string $pdoDsn,
        string $pdoTable,
        string $localStateRoot,
        string $coordinationConcern,
    ): DiscoveryStateStoreDescriptor {
        if (strtolower(trim($backend)) === 'pdo') {
            $dsn = trim($pdoDsn);
            $sharedConfigured = $dsn !== '';
            $sqliteDsn = str_starts_with(strtolower($dsn), 'sqlite:');

            $concerns = ['database-coordination-store', $coordinationConcern];
            if ($sqliteDsn) {
                $concerns[] = 'sqlite-single-writer';
            }

            return new DiscoveryStateStoreDescriptor(
                name: $name,
                backend: 'pdo_table',
                path: $dsn === '' ? sprintf('pdo:%s', $pdoTable) : sprintf('%s#%s', $dsn, $pdoTable),
                storageMode: 'database',
                sharedConfigured: $sharedConfigured,
                multiReplicaWriteReady: $sharedConfigured && !$sqliteDsn,
                concerns: $concerns,
            );
        }

        return $this->jsonStore($name, $path, $localStateRoot);
    }

    private function rateLimitStore(string $localStateRoot): DiscoveryStateStoreDescriptor
    {
        return $this->coordinationStore(
            name: 'rateLimitStore',
            path: $this->rateLimitStorePath,
            backend: $this->rateLimitBackend,
            pdoDsn: $this->rateLimitPdoDsn,
            pdoTable: $this->rateLimitPdoTable,
            localStateRoot: $localStateRoot,
            coordinationConcern: 'shared-counter-coordination',
        );
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

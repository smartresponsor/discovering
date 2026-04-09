<?php

declare(strict_types=1);

namespace App\Service\Discovery\Diagnostics;

use App\Dto\Discovery\DiscoveryBackendProbeResult;
use App\Dto\Discovery\DiscoveryBackendReachabilityReport;


/**
 * Builds the discovery backend reachability output used by discovery management or diagnostics flows.
 */
final class DiscoveryBackendReachabilityBuilder
{
    public function __construct(
        private readonly DiscoveryProbeTransportInterface $transport,
        private readonly string $indexBackend,
        private readonly string $meiliUrl,
        private readonly string $meiliApiKey,
        private readonly string $feedbackBackend,
        private readonly string $feedbackPdoDsn,
        private readonly ?string $feedbackPdoUser,
        private readonly ?string $feedbackPdoPassword,
        private readonly string $operationLogBackend,
        private readonly string $operationLogPdoDsn,
        private readonly ?string $operationLogPdoUser,
        private readonly ?string $operationLogPdoPassword,
        private readonly string $rebuildEvidenceBackend,
        private readonly string $rebuildEvidencePdoDsn,
        private readonly ?string $rebuildEvidencePdoUser,
        private readonly ?string $rebuildEvidencePdoPassword,
        private readonly string $libsourceEventLogBackend,
        private readonly string $libsourceEventLogPdoDsn,
        private readonly ?string $libsourceEventLogPdoUser,
        private readonly ?string $libsourceEventLogPdoPassword,
        private readonly string $rateLimitBackend,
        private readonly string $rateLimitPdoDsn,
        private readonly ?string $rateLimitPdoUser,
        private readonly ?string $rateLimitPdoPassword,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryBackendReachabilityReport
    {
        $probes = [
            $this->discoveryIndexProbe(),
            $this->pdoBackedStoreProbe('feedbackStore', $this->feedbackBackend, $this->feedbackPdoDsn, $this->feedbackPdoUser, $this->feedbackPdoPassword),
            $this->pdoBackedStoreProbe('operationLog', $this->operationLogBackend, $this->operationLogPdoDsn, $this->operationLogPdoUser, $this->operationLogPdoPassword),
            $this->pdoBackedStoreProbe('rebuildEvidence', $this->rebuildEvidenceBackend, $this->rebuildEvidencePdoDsn, $this->rebuildEvidencePdoUser, $this->rebuildEvidencePdoPassword),
            $this->pdoBackedStoreProbe('libsourceEventLog', $this->libsourceEventLogBackend, $this->libsourceEventLogPdoDsn, $this->libsourceEventLogPdoUser, $this->libsourceEventLogPdoPassword),
            $this->pdoBackedStoreProbe('rateLimitStore', $this->rateLimitBackend, $this->rateLimitPdoDsn, $this->rateLimitPdoUser, $this->rateLimitPdoPassword),
        ];

        $performedProbeCount = 0;
        $reachableProbeCount = 0;
        $failingProbeCount = 0;
        $skippedProbeCount = 0;
        $failingProbeNames = [];
        $notes = [];

        foreach ($probes as $probe) {
            if (in_array($probe->status, ['local_only', 'not_configured'], true)) {
                ++$skippedProbeCount;
                continue;
            }

            ++$performedProbeCount;
            if ($probe->status === 'reachable') {
                ++$reachableProbeCount;
                continue;
            }

            ++$failingProbeCount;
            $failingProbeNames[] = $probe->name;
        }

        if ($performedProbeCount === 0) {
            $overallStatus = 'not_configured';
            $recommendedAction = 'No shared backends are configured yet. Keep operating in local-only mode until at least one shared backend is configured.';
            $notes[] = 'No shared backend probes were performed because every configured store remains in local-only mode.';
        } elseif ($failingProbeCount === 0) {
            $overallStatus = 'healthy';
            $recommendedAction = 'All configured shared backends responded successfully.';
            $notes[] = 'All performed backend reachability probes completed successfully.';
        } else {
            $overallStatus = 'degraded';
            $recommendedAction = sprintf(
                'Review the failing backend reachability probes before relying on shared operation: %s.',
                implode(', ', $failingProbeNames),
            );
            $notes[] = sprintf('%d backend reachability probe(s) failed.', $failingProbeCount);
        }

        return new DiscoveryBackendReachabilityReport(
            checkedAt: gmdate(DATE_ATOM),
            performedProbeCount: $performedProbeCount,
            reachableProbeCount: $reachableProbeCount,
            failingProbeCount: $failingProbeCount,
            skippedProbeCount: $skippedProbeCount,
            overallStatus: $overallStatus,
            recommendedAction: $recommendedAction,
            probes: $probes,
            failingProbeNames: $failingProbeNames,
            notes: $notes,
        );
    }

    private function discoveryIndexProbe(): DiscoveryBackendProbeResult
    {
        if (strtolower(trim($this->indexBackend)) !== 'meili') {
            return new DiscoveryBackendProbeResult(
                name: 'discoveryIndex',
                backend: 'sqlite',
                target: 'local-sqlite',
                status: 'local_only',
                details: ['Active discovery index backend is local SQLite FTS; no remote reachability probe is required.'],
            );
        }

        $target = trim($this->meiliUrl);
        if ($target === '') {
            return new DiscoveryBackendProbeResult(
                name: 'discoveryIndex',
                backend: 'meilisearch',
                target: 'unconfigured',
                status: 'not_configured',
                details: ['APP_DISCOVERY_MEILI_URL is empty while the discovery index backend is set to meili.'],
            );
        }

        $result = $this->transport->probeHttp($target, $this->meiliApiKey);

        return new DiscoveryBackendProbeResult(
            name: 'discoveryIndex',
            backend: 'meilisearch',
            target: $target,
            status: $result['reachable'] ? 'reachable' : 'unreachable',
            details: $result['details'],
        );
    }

    private function pdoBackedStoreProbe(string $name, string $backend, string $dsn, ?string $user, ?string $password): DiscoveryBackendProbeResult
    {
        $normalizedBackend = strtolower(trim($backend));
        if ($normalizedBackend !== 'pdo') {
            return new DiscoveryBackendProbeResult(
                name: $name,
                backend: $normalizedBackend === '' ? 'unknown' : $normalizedBackend,
                target: 'local-default',
                status: 'local_only',
                details: [sprintf('%s currently uses a local or file-oriented backend; no shared PDO reachability probe is required.', $name)],
            );
        }

        if (trim($dsn) === '') {
            return new DiscoveryBackendProbeResult(
                name: $name,
                backend: 'pdo_table',
                target: 'unconfigured',
                status: 'not_configured',
                details: [sprintf('%s uses the PDO backend but its DSN is empty.', $name)],
            );
        }

        $result = $this->transport->probePdo($dsn, $user, $password);

        return new DiscoveryBackendProbeResult(
            name: $name,
            backend: 'pdo_table',
            target: $dsn,
            status: $result['reachable'] ? 'reachable' : 'unreachable',
            details: $result['details'],
        );
    }
}

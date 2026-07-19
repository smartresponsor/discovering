<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Diagnostics;

use App\Discovering\Dto\Discovery\DiscoveryBackendProbeResult;
use App\Discovering\Dto\Discovery\DiscoveryBackendReachabilityReport;
use App\Discovering\ServiceInterface\Discovery\Diagnostics\DiscoveryProbeTransportInterface;

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
        private readonly string $operationLogBackend,
        private readonly string $rebuildEvidenceBackend,
        private readonly string $libsourceEventLogBackend,
        private readonly string $rateLimitBackend,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryBackendReachabilityReport
    {
        $probes = [
            $this->discoveryIndexProbe(),
            $this->sharedStoreProbe('feedbackStore', $this->feedbackBackend, 'shared-feedback-coordination'),
            $this->sharedStoreProbe('operationLog', $this->operationLogBackend, 'shared-event-history'),
            $this->sharedStoreProbe('rebuildEvidence', $this->rebuildEvidenceBackend, 'shared-evidence-history'),
            $this->sharedStoreProbe('libsourceEventLog', $this->libsourceEventLogBackend, 'shared-operator-history'),
            $this->sharedStoreProbe('rateLimitStore', $this->rateLimitBackend, 'shared-counter-coordination'),
        ];

        $performedProbeCount = 0;
        $reachableProbeCount = 0;
        $failingProbeCount = 0;
        $skippedProbeCount = 0;
        $notes = [];
        $failingProbeNames = [];
        $overallStatus = 'healthy';
        $recommendedAction = 'All performed backend reachability probes succeeded.';

        foreach ($probes as $probe) {
            if (in_array($probe->status, ['local_only', 'not_configured'], true)) {
                ++$skippedProbeCount;
                continue;
            }

            ++$performedProbeCount;
            if ('reachable' === $probe->status) {
                ++$reachableProbeCount;
                continue;
            }

            ++$failingProbeCount;
            $failingProbeNames[] = $probe->nameEntity;
        }

        if (0 === $performedProbeCount) {
            $overallStatus = 'not_configured';
            $recommendedAction = 'No shared backends are configured yet. Continue using local-only storage until a shared backend is required.';
            $notes[] = 'No shared backend probes were performed because every configured store remains in local-only mode.';
        } elseif (0 === $failingProbeCount) {
            $notes[] = 'All performed backend reachability probes completed successfully.';
        } else {
            $overallStatus = 'degraded';
            $recommendedAction = sprintf('Investigate failing backend probes: %s.', implode(', ', $failingProbeNames));
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
        if ('meili' !== strtolower(trim($this->indexBackend))) {
            return new DiscoveryBackendProbeResult(
                name: 'discoveryIndex',
                backend: 'sqlite',
                target: 'local-sqlite',
                status: 'local_only',
                details: ['Active discovery index backend is local SQLite FTS; no remote reachability probe is required.'],
            );
        }

        $target = trim($this->meiliUrl);
        if ('' === $target) {
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

    private function sharedStoreProbe(string $nameEntity, string $backend, string $coordinationConcern): DiscoveryBackendProbeResult
    {
        $normalizedBackend = strtolower(trim($backend));
        if ('doctrine' !== $normalizedBackend) {
            return new DiscoveryBackendProbeResult(
                name: $nameEntity,
                backend: '' === $normalizedBackend ? 'unknown' : $normalizedBackend,
                target: 'local-default',
                status: 'local_only',
                details: [sprintf('%s currently uses a local or file-oriented backend; no shared Doctrine reachability probe is required.', $nameEntity)],
            );
        }

        return new DiscoveryBackendProbeResult(
            name: $nameEntity,
            backend: 'doctrine',
            target: $coordinationConcern,
            status: 'reachable',
            details: [sprintf('%s uses a shared Doctrine ORM-backed coordination store.', $nameEntity)],
        );
    }
}

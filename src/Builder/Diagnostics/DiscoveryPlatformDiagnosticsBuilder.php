<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Diagnostics;

use App\Discovering\Builder\Rebuild\DiscoveryRollbackPlanBuilder;
use App\Discovering\Builder\Topology\DiscoveryStateTopologyBuilder;
use App\Discovering\DTO\DiscoveryPlatformDiagnosticsDTO;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryStagingCapableBackendInterface;

/**
 * Builds the discovery platform diagnostics output used by discovery management or diagnostics flows.
 */
final class DiscoveryPlatformDiagnosticsBuilder
{
    public function __construct(
        private readonly DiscoveryBackendInterface $adapter,
        private readonly DiscoveryStateTopologyBuilder $stateTopologyBuilder,
        private readonly DiscoveryRollbackPlanBuilder $rollbackPlanBuilder,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryPlatformDiagnosticsDTO
    {
        $topology = $this->stateTopologyBuilder->build();
        $rollbackPlan = $this->rollbackPlanBuilder->build();
        $backendName = $this->adapter->getBackendName();
        $stagedRebuildSupported = $this->adapter instanceof DiscoveryStagingCapableBackendInterface
            && $this->adapter->supportsStagedRebuild();

        $storeBackends = [];
        $multiReplicaWriteReadyStores = [];
        $blockingStores = [];

        foreach ($topology->stores as $store) {
            $storeBackends[$store->nameEntity] = $store->backend;

            if ($store->multiReplicaWriteReady) {
                $multiReplicaWriteReadyStores[] = $store->nameEntity;
                continue;
            }

            $blockingStores[] = $store->nameEntity;
        }

        $notes = array_values($topology->notes);
        $notes[] = sprintf('Active discovery backend is %s.', $backendName);

        if ($stagedRebuildSupported) {
            $notes[] = 'Active discovery backend supports staged rebuild / alias promotion.';
        } else {
            $notes[] = 'Active discovery backend does not support staged rebuild / alias promotion.';
        }

        if ($topology->distributedReady && !$stagedRebuildSupported) {
            $notes[] = 'Distributed-ready coordination is configured, but rebuild cutover still depends on backend-specific promotion semantics.';
        }

        if (!$topology->distributedReady && $stagedRebuildSupported) {
            $notes[] = 'Staged rebuild is available, but overall distributed readiness remains blocked by one or more mutable discovery stores.';
        }

        if ($rollbackPlan->rollbackReady) {
            $notes[] = 'Rollback posture is currently ready.';
        } else {
            $notes[] = sprintf('Rollback posture is currently %s.', $rollbackPlan->status);
        }

        $postureStatus = 'degraded';
        $riskLevel = 'medium';
        $recommendedAction = 'Review platform notes and resolve blocking discovery stores before relying on multi-replica operation.';

        if (!$topology->sharedStateConfigured) {
            $postureStatus = 'local_only';
            $riskLevel = 'medium';
            $recommendedAction = 'Platform remains in local single-node mode. Configure shared coordination backends before targeting multi-replica deployment.';
        } elseif ($topology->distributedReady && $stagedRebuildSupported && $rollbackPlan->rollbackReady) {
            $postureStatus = 'healthy';
            $riskLevel = 'low';
            $recommendedAction = 'Platform posture is ready for shared multi-replica operation.';
        } elseif ($topology->distributedReady) {
            $postureStatus = 'degraded';
            $riskLevel = 'medium';
            if (!$stagedRebuildSupported) {
                $recommendedAction = 'Shared coordination is configured, but rebuild cutover still depends on backend-specific promotion semantics. Verify deployment cutover playbooks before multi-replica rollout.';
            } elseif (!$rollbackPlan->rollbackReady) {
                $recommendedAction = 'Shared coordination is configured, but rollback posture is not ready. Build fresh staged evidence before relying on multi-replica rollout.';
            } else {
                $recommendedAction = 'Shared coordination is configured. Review remaining platform notes before rollout.';
            }
        } elseif ([] !== $blockingStores) {
            $postureStatus = 'transitioning';
            $riskLevel = 'high';
            $recommendedAction = sprintf('Complete stronger coordination for blocking stores: %s.', implode(', ', $blockingStores));
        }

        return new DiscoveryPlatformDiagnosticsDTO(
            backendName: $backendName,
            indexStoreBackend: $storeBackends['discoveryIndex'] ?? 'unknown',
            stagedRebuildSupported: $stagedRebuildSupported,
            sharedStateConfigured: $topology->sharedStateConfigured,
            distributedReady: $topology->distributedReady,
            rollbackStatus: $rollbackPlan->status,
            rollbackReady: $rollbackPlan->rollbackReady,
            coordinationReadyStoreCount: count($multiReplicaWriteReadyStores),
            postureStatus: $postureStatus,
            riskLevel: $riskLevel,
            recommendedAction: $recommendedAction,
            storeBackends: $storeBackends,
            multiReplicaWriteReadyStores: $multiReplicaWriteReadyStores,
            blockingStores: $blockingStores,
            notes: array_values(array_unique($notes)),
        );
    }
}

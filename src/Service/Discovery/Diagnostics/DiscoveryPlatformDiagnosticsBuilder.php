<?php

declare(strict_types=1);

namespace App\Service\Discovery\Diagnostics;

use App\Dto\Discovery\DiscoveryPlatformDiagnostics;
use App\Service\Discovery\Rebuild\DiscoveryRollbackPlanBuilder;
use App\Service\Discovery\Topology\DiscoveryStateTopologyBuilder;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableAdapterInterface;

final class DiscoveryPlatformDiagnosticsBuilder
{
    public function __construct(
        private readonly DiscoveryAdapterInterface $adapter,
        private readonly DiscoveryStateTopologyBuilder $stateTopologyBuilder,
        private readonly DiscoveryRollbackPlanBuilder $rollbackPlanBuilder,
    ) {
    }

    public function build(): DiscoveryPlatformDiagnostics
    {
        $topology = $this->stateTopologyBuilder->build();
        $rollbackPlan = $this->rollbackPlanBuilder->build();
        $backendName = $this->adapter->getBackendName();
        $stagedRebuildSupported = $this->adapter instanceof DiscoveryStagingCapableAdapterInterface
            && $this->adapter->supportsStagedRebuild();

        $storeBackends = [];
        $multiReplicaWriteReadyStores = [];
        $blockingStores = [];

        foreach ($topology->stores as $store) {
            $storeBackends[$store->name] = $store->backend;

            if ($store->multiReplicaWriteReady) {
                $multiReplicaWriteReadyStores[] = $store->name;
                continue;
            }

            $blockingStores[] = $store->name;
        }

        $notes = array_values($topology->notes);
        $notes[] = sprintf('Active discovery adapter backend is %s.', $backendName);

        if ($stagedRebuildSupported) {
            $notes[] = 'Active discovery adapter supports staged rebuild / alias promotion.';
        } else {
            $notes[] = 'Active discovery adapter does not support staged rebuild / alias promotion.';
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

        return new DiscoveryPlatformDiagnostics(
            backendName: $backendName,
            indexStoreBackend: $storeBackends['discoveryIndex'] ?? 'unknown',
            stagedRebuildSupported: $stagedRebuildSupported,
            sharedStateConfigured: $topology->sharedStateConfigured,
            distributedReady: $topology->distributedReady,
            rollbackStatus: $rollbackPlan->status,
            rollbackReady: $rollbackPlan->rollbackReady,
            coordinationReadyStoreCount: count($multiReplicaWriteReadyStores),
            storeBackends: $storeBackends,
            multiReplicaWriteReadyStores: $multiReplicaWriteReadyStores,
            blockingStores: $blockingStores,
            notes: array_values(array_unique($notes)),
        );
    }
}

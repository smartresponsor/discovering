<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rollback;

use App\Dto\Discovery\DiscoveryRollbackExecutionResult;
use App\Service\Discovery\Rebuild\DiscoveryRollbackPlanBuilder;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableAdapterInterface;

final class DiscoveryRollbackExecutor
{
    public function __construct(
        private readonly DiscoveryRollbackPlanBuilder $rollbackPlanBuilder,
        private readonly DiscoveryAdapterInterface $adapter,
    ) {
    }

    public function execute(?string $expectedCurrentEvidenceId = null, ?string $expectedTargetEvidenceId = null): DiscoveryRollbackExecutionResult
    {
        $plan = $this->rollbackPlanBuilder->build();
        $backendName = $this->adapter->getBackendName();

        if (!$plan->rollbackReady) {
            return new DiscoveryRollbackExecutionResult(
                executed: false,
                status: 'plan_not_ready',
                backendName: $backendName,
                currentEvidenceId: $plan->currentEvidenceId,
                targetEvidenceId: $plan->previousEvidenceId,
                alias: 'global',
                targetPhysicalIndex: $plan->rollbackTargetPhysicalIndex,
                notes: $plan->notes,
            );
        }

        if ($expectedCurrentEvidenceId !== null && $expectedCurrentEvidenceId !== $plan->currentEvidenceId) {
            return new DiscoveryRollbackExecutionResult(
                executed: false,
                status: 'current_evidence_mismatch',
                backendName: $backendName,
                currentEvidenceId: $plan->currentEvidenceId,
                targetEvidenceId: $plan->previousEvidenceId,
                alias: 'global',
                targetPhysicalIndex: $plan->rollbackTargetPhysicalIndex,
                notes: [sprintf('Requested current evidence "%s" does not match latest rollback plan evidence "%s".', $expectedCurrentEvidenceId, $plan->currentEvidenceId ?? 'n/a')],
            );
        }

        if ($expectedTargetEvidenceId !== null && $expectedTargetEvidenceId !== $plan->previousEvidenceId) {
            return new DiscoveryRollbackExecutionResult(
                executed: false,
                status: 'target_evidence_mismatch',
                backendName: $backendName,
                currentEvidenceId: $plan->currentEvidenceId,
                targetEvidenceId: $plan->previousEvidenceId,
                alias: 'global',
                targetPhysicalIndex: $plan->rollbackTargetPhysicalIndex,
                notes: [sprintf('Requested target evidence "%s" does not match rollback plan target evidence "%s".', $expectedTargetEvidenceId, $plan->previousEvidenceId ?? 'n/a')],
            );
        }

        if (!$this->adapter instanceof DiscoveryStagingCapableAdapterInterface || !$this->adapter->supportsStagedRebuild()) {
            return new DiscoveryRollbackExecutionResult(
                executed: false,
                status: 'backend_not_staging_capable',
                backendName: $backendName,
                currentEvidenceId: $plan->currentEvidenceId,
                targetEvidenceId: $plan->previousEvidenceId,
                alias: 'global',
                targetPhysicalIndex: $plan->rollbackTargetPhysicalIndex,
                notes: ['Configured discovery backend does not expose staged alias swap capabilities required for rollback execution.'],
            );
        }

        if ($plan->rollbackTargetPhysicalIndex === null) {
            return new DiscoveryRollbackExecutionResult(
                executed: false,
                status: 'missing_target_physical_index',
                backendName: $backendName,
                currentEvidenceId: $plan->currentEvidenceId,
                targetEvidenceId: $plan->previousEvidenceId,
                alias: 'global',
                targetPhysicalIndex: null,
                notes: ['Rollback plan is marked ready, but no target physical index is available to promote.'],
            );
        }

        $this->adapter->swapAlias('global', $plan->rollbackTargetPhysicalIndex);

        return new DiscoveryRollbackExecutionResult(
            executed: true,
            status: 'rollback_executed',
            backendName: $backendName,
            currentEvidenceId: $plan->currentEvidenceId,
            targetEvidenceId: $plan->previousEvidenceId,
            alias: 'global',
            targetPhysicalIndex: $plan->rollbackTargetPhysicalIndex,
            notes: [
                'Rollback alias swap was executed against the current discovery backend.',
                'Run query smoke checks and management exports immediately after rollback promotion.',
            ],
        );
    }
}

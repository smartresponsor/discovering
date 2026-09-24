<?php

declare(strict_types=1);

namespace App\Discovering\Service\Rollback;

use App\Discovering\Builder\Rebuild\DiscoveryRollbackPlanBuilder;
use App\Discovering\DTO\DiscoveryRollbackExecutionResultDTO;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryStagingCapableBackendInterface;

/**
 * Provides the discovery rollback executor capability within the discovery component.
 */
final class DiscoveryRollbackExecutor
{
    public function __construct(
        private readonly DiscoveryRollbackPlanBuilder $rollbackPlanBuilder,
        private readonly DiscoveryBackendInterface $adapter,
    ) {
    }

    /**
     * Performs the execute operation for this discovery service.
     */
    public function execute(?string $expectedCurrentEvidenceId = null, ?string $expectedTargetEvidenceId = null): DiscoveryRollbackExecutionResultDTO
    {
        $plan = $this->rollbackPlanBuilder->build();
        $backendName = $this->adapter->getBackendName();

        if (!$plan->rollbackReady) {
            return new DiscoveryRollbackExecutionResultDTO(
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

        if (null !== $expectedCurrentEvidenceId && $expectedCurrentEvidenceId !== $plan->currentEvidenceId) {
            return new DiscoveryRollbackExecutionResultDTO(
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

        if (null !== $expectedTargetEvidenceId && $expectedTargetEvidenceId !== $plan->previousEvidenceId) {
            return new DiscoveryRollbackExecutionResultDTO(
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

        if (!$this->adapter instanceof DiscoveryStagingCapableBackendInterface || !$this->adapter->supportsStagedRebuild()) {
            return new DiscoveryRollbackExecutionResultDTO(
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

        if (null === $plan->rollbackTargetPhysicalIndex) {
            return new DiscoveryRollbackExecutionResultDTO(
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

        return new DiscoveryRollbackExecutionResultDTO(
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

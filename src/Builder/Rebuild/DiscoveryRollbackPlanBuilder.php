<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Rebuild;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\DTO\DiscoveryRollbackPlanDTO;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryRebuildEvidenceStoreInterface;

/**
 * Builds the discovery rollback plan output used by discovery management or diagnostics flows.
 */
final class DiscoveryRollbackPlanBuilder
{
    public function __construct(
        private readonly DiscoveryRebuildEvidenceStoreInterface $rebuildEvidenceStore,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryRollbackPlanDTO
    {
        $evidence = $this->rebuildEvidenceStore->latest(10);
        $globalEvidence = array_values(array_filter(
            $evidence,
            static fn (DiscoveryRebuildSummaryDTO $summary): bool => 'global' === $summary->resource,
        ));

        $current = $globalEvidence[0] ?? null;
        $previous = $globalEvidence[1] ?? null;

        if (!$current instanceof DiscoveryRebuildSummaryDTO) {
            return new DiscoveryRollbackPlanDTO(
                rollbackReady: false,
                status: 'no_evidence',
                currentEvidenceId: null,
                previousEvidenceId: null,
                currentPhysicalIndex: null,
                rollbackTargetPhysicalIndex: null,
                recommendedCommand: null,
                notes: ['No global rebuild evidence is recorded yet. Run app:discovery:rebuild before planning rollback.'],
            );
        }

        $currentPhysicalIndex = $this->resolvePhysicalIndex($current);
        $previousPhysicalIndex = $previous instanceof DiscoveryRebuildSummaryDTO ? $this->resolvePhysicalIndex($previous) : null;

        if (null === $currentPhysicalIndex) {
            return new DiscoveryRollbackPlanDTO(
                rollbackReady: false,
                status: 'current_index_unknown',
                currentEvidenceId: $current->evidenceId,
                previousEvidenceId: $previous?->evidenceId,
                currentPhysicalIndex: null,
                rollbackTargetPhysicalIndex: $previousPhysicalIndex,
                recommendedCommand: null,
                notes: ['The latest rebuild evidence does not describe a physical global index target.'],
            );
        }

        if (null === $previousPhysicalIndex) {
            return new DiscoveryRollbackPlanDTO(
                rollbackReady: false,
                status: 'no_previous_candidate',
                currentEvidenceId: $current->evidenceId,
                previousEvidenceId: $previous?->evidenceId,
                currentPhysicalIndex: $currentPhysicalIndex,
                rollbackTargetPhysicalIndex: null,
                recommendedCommand: null,
                notes: ['Rollback requires at least one earlier global rebuild evidence record with a known physical index.'],
            );
        }

        if ($currentPhysicalIndex === $previousPhysicalIndex) {
            return new DiscoveryRollbackPlanDTO(
                rollbackReady: false,
                status: 'already_on_previous_target',
                currentEvidenceId: $current->evidenceId,
                previousEvidenceId: $previous->evidenceId,
                currentPhysicalIndex: $currentPhysicalIndex,
                rollbackTargetPhysicalIndex: $previousPhysicalIndex,
                recommendedCommand: null,
                notes: ['The latest two global rebuilds resolve to the same physical index, so there is no distinct rollback target to promote.'],
            );
        }

        $notes = [];
        if (!$current->aliasSwapApplied || 'staged_alias_swap' !== $current->deploymentMode) {
            $notes[] = 'Latest rebuild did not complete as a staged alias swap; rollback execution may be blocked or require a different target posture.';
        }
        $notes[] = 'Validate query smoke checks and management export health before and after performing rollback.';

        return new DiscoveryRollbackPlanDTO(
            rollbackReady: true,
            status: 'plan_ready',
            currentEvidenceId: $current->evidenceId,
            previousEvidenceId: $previous->evidenceId,
            currentPhysicalIndex: $currentPhysicalIndex,
            rollbackTargetPhysicalIndex: $previousPhysicalIndex,
            recommendedCommand: sprintf('app:discovery:rollback:execute --current=%s --target=%s', $current->evidenceId, $previous->evidenceId),
            notes: $notes,
        );
    }

    private function resolvePhysicalIndex(DiscoveryRebuildSummaryDTO $summary): ?string
    {
        $index = $summary->stagedIndexes['global'] ?? null;
        if (is_string($index) && '' !== $index) {
            return $index;
        }

        if ('global' === $summary->resource && 'in_place' === $summary->deploymentMode) {
            return 'discovering';
        }

        return null;
    }
}

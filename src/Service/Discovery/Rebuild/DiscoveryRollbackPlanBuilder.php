<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use App\Dto\Discovery\DiscoveryRollbackPlan;

final class DiscoveryRollbackPlanBuilder
{
    public function __construct(
        private readonly DiscoveryRebuildEvidenceStoreInterface $rebuildEvidenceStore,
    ) {
    }

    public function build(): DiscoveryRollbackPlan
    {
        $evidence = $this->rebuildEvidenceStore->latest(10);
        $globalEvidence = array_values(array_filter(
            $evidence,
            static fn (DiscoveryRebuildSummary $summary): bool => $summary->resource === 'global',
        ));

        $current = $globalEvidence[0] ?? null;
        $previous = $globalEvidence[1] ?? null;

        if (!$current instanceof DiscoveryRebuildSummary) {
            return new DiscoveryRollbackPlan(
                rollbackReady: false,
                status: 'no_evidence',
                currentEvidenceId: null,
                previousEvidenceId: null,
                currentPhysicalIndex: null,
                rollbackTargetPhysicalIndex: null,
                recommendedCommand: null,
                notes: ['No global rebuild evidence is recorded yet. Run discovering:rebuild before planning rollback.'],
            );
        }

        $currentPhysicalIndex = $this->resolvePhysicalIndex($current);
        $previousPhysicalIndex = $previous instanceof DiscoveryRebuildSummary ? $this->resolvePhysicalIndex($previous) : null;

        if ($currentPhysicalIndex === null) {
            return new DiscoveryRollbackPlan(
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

        if ($previousPhysicalIndex === null) {
            return new DiscoveryRollbackPlan(
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
            return new DiscoveryRollbackPlan(
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
        if (!$current->aliasSwapApplied || $current->deploymentMode !== 'staged_alias_swap') {
            $notes[] = 'Latest rebuild did not complete as a staged alias swap; rollback remains a manual operator action.';
        }
        $notes[] = 'Validate query smoke checks and management export health before and after performing rollback.';

        return new DiscoveryRollbackPlan(
            rollbackReady: true,
            status: 'plan_ready',
            currentEvidenceId: $current->evidenceId,
            previousEvidenceId: $previous->evidenceId,
            currentPhysicalIndex: $currentPhysicalIndex,
            rollbackTargetPhysicalIndex: $previousPhysicalIndex,
            recommendedCommand: sprintf('discovering:rollback:plan --current=%s --target=%s', $current->evidenceId, $previous->evidenceId),
            notes: $notes,
        );
    }

    private function resolvePhysicalIndex(DiscoveryRebuildSummary $summary): ?string
    {
        $index = $summary->stagedIndexes['global'] ?? null;
        if (is_string($index) && $index !== '') {
            return $index;
        }

        if ($summary->resource === 'global' && $summary->deploymentMode === 'in_place') {
            return 'discovering';
        }

        return null;
    }
}

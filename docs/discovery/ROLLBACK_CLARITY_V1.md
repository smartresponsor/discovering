# Discovery Rollback Clarity v1

The repository now distinguishes three separate concepts:

1. **Rebuild evidence** — historical record of what was indexed and how.
2. **Rollback planning** — current operator posture derived from recent global rebuild evidence.
3. **Rollback execution** — available as an explicit alias-promotion primitive when rollback posture is ready.

## Current contract

`GET /management/discovery/rollback/export` returns a rollback plan with:

- `rollbackReady`
- `status`
- `currentEvidenceId`
- `previousEvidenceId`
- `currentPhysicalIndex`
- `rollbackTargetPhysicalIndex`
- `recommendedCommand`
- `notes`

## Status semantics

- `no_evidence` — no global rebuild history exists yet
- `no_previous_candidate` — there is a current global rebuild but no earlier rollback target
- `current_index_unknown` — evidence exists but does not resolve a usable physical index target
- `already_on_previous_target` — latest two rebuild records converge on the same physical target
- `plan_ready` — operator rollback candidate is available

## Important limitation

`recommendedCommand` now points to the guarded execution primitive. Rollback remains intentionally narrow: it promotes an earlier physical index alias target, but it does not reconstruct historical document state.


## Execution status

Rollback is no longer plan-only. Alias rollback execution now exists as an explicit primitive, while historical content recreation still remains out of scope.

# Discovery platform diagnostics v1

The platform diagnostics surface provides a structural operator-facing verdict for the currently selected discovery backend posture.

It reports:

- active discovery backend
- index store backend
- staged rebuild support
- shared-state posture
- distributed-readiness posture
- rollback posture
- platform posture status
- risk level
- recommended action
- blocking mutable stores

## Posture status semantics

`postureStatus` values:

- `healthy` — shared coordination is configured, distributed readiness is true, staged rebuild support is available, and rollback posture is ready.
- `degraded` — a stronger platform mode is partially configured, but one or more rollout or rollback constraints still matter.
- `transitioning` — stronger coordination has started, but blocking mutable stores still prevent multi-replica posture.
- `local_only` — the repository is still effectively in single-node local mode.

## Risk level semantics

- `low` — the structural posture is ready for shared multi-replica operation.
- `medium` — the platform is usable, but rollout or rollback caveats still matter.
- `high` — the platform is mid-transition and still blocked by one or more mutable stores.

This surface does not claim that every backend is reachable at runtime. It reports the repository's currently selected discovery platform posture and the structural blockers that still matter for multi-replica operation. Reachability is handled by the dedicated platform probe surfaces described in `PLATFORM_PROBES_V1.md`.

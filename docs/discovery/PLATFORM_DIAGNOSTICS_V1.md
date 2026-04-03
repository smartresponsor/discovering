# Discovery platform diagnostics v1

## Purpose

This surface summarizes the active discovery adapter, mutable-state topology posture, rebuild capability, and rollback readiness in one operator-facing snapshot.

## Covered signals

- active adapter backend name
- effective index store backend
- staged rebuild / alias-promotion capability
- shared-state configured posture
- distributed-ready posture
- rollback status / rollback-ready signal
- store backend map
- blocking stores that still prevent multi-replica write readiness

## Surfaces

- CLI: `app:discovery:platform:diagnose`
- management export: `/management/discovery/platform/export`
- management overview: `Platform diagnostics`

## Intent

This surface does not claim that every backend is reachable at runtime. It reports the repository's currently selected discovery platform posture and the structural blockers that still matter for multi-replica operation. Reachability is now handled by the dedicated platform probe surfaces described in `PLATFORM_PROBES_V1.md`.

# Discovery platform probes v1

The platform probe surface provides on-demand reachability checks for configured shared discovery backends.

It is intentionally separated from the overview and structural diagnostics surfaces so that operator pages do not perform network or database connectivity checks on every load.

The probe report includes:

- check timestamp
- number of performed, reachable, failing, and skipped probes
- overall reachability status
- recommended action
- failing probe names
- per-backend probe results with details

## Probe semantics

Probe status values:

- `reachable` — the target backend responded successfully to a lightweight reachability check.
- `unreachable` — the target backend is configured, but the probe failed.
- `local_only` — the target backend remains in local single-node mode, so no shared-backend reachability probe is attempted.

## Overall status semantics

- `healthy` — every performed shared-backend probe succeeded.
- `degraded` — at least one shared-backend probe failed, but not all performed probes failed.
- `unreachable` — every performed shared-backend probe failed.
- `not_configured` — no shared-backend probes were performed because every backend remains in local-only mode.

These probes are operator-facing diagnostics only. They do not mutate platform state and they do not attempt deeper functional validation beyond lightweight reachability.

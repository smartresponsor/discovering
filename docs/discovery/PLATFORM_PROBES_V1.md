# Discovery platform probes v1

The discovery platform now exposes explicit backend reachability probes for the currently configured shared backends.

## Surfaces

- CLI: `app:discovery:platform:probe`
- Management export: `GET /management/discovery/platform/probes/export`

## What is probed

- active discovery index service when `APP_DISCOVERY_INDEX_BACKEND=meili`
- feedback coordination backend when `APP_DISCOVERY_FEEDBACK_BACKEND=pdo`
- operation log coordination backend when `APP_DISCOVERY_OPERATION_LOG_BACKEND=pdo`
- rebuild evidence coordination backend when `APP_DISCOVERY_REBUILD_EVIDENCE_BACKEND=pdo`
- libsource event coordination backend when `APP_DISCOVERY_LIBSOURCE_EVENT_LOG_BACKEND=pdo`
- rate-limit coordination backend when `APP_DISCOVERY_RATE_LIMIT_BACKEND=pdo`

## Probe semantics

Probe status values:

- `reachable` — the configured shared backend responded successfully
- `unreachable` — the configured shared backend was contacted but did not respond successfully
- `not_configured` — the selected shared backend is missing required connection parameters
- `local_only` — the current store remains local/file/SQLite-oriented, so no shared reachability probe is required

The probe surface is intentionally separate from the general platform diagnostics surface so that operator-facing overview pages do not need to perform network or database reachability checks on every page load.

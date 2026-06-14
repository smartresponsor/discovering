# Discovering Canonization Wave 29 — Observability / Operations Taxonomy

Wave 29 documents and machine-checks the Discovering observability and operation-event posture.

## Scope

Touched files:

- `docs/discovery/OBSERVABILITY_POSTURE.md`
- `docs/discovery/CANONIZATION_WAVE29_OBSERVABILITY_OPERATIONS_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Canonical posture

Observability responsibility is split across:

- request correlation subscriber,
- operation logger,
- operation event DTO,
- operation event persistence entity,
- operation event log store interface,
- configurable/file/doctrine operation event stores,
- operation event JSON serializer,
- config-level backend/path selection.

## Non-goals

- No logger behavior changes.
- No store behavior changes.
- No event schema changes.
- No route/controller changes.
- No runtime dependency changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected observability counter:

```text
wave29 observability operations findings: 0
```

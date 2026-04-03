# Discovery API contract v1

This document defines the current HTTP contract baseline for `Discovering` JSON surfaces.

## Envelope guarantees

Every JSON response emitted by discovery public API and discovery management exports must contain:

- `ok`
- `apiVersion`
- `requestId`
- `meta`
- `data` on success or `error` on failure

The `meta` section must contain:

- `canonicalPath`
- `deprecatedAlias`
- `schemaFamily`
- `schemaVersion`

Current values:

- `apiVersion = v1`
- `schemaFamily = discovery-api-envelope`
- `schemaVersion = 1.0.0`

## Evolution policy

- Additive fields may be introduced inside `meta`, `data`, and `error.details` without changing the API version.
- Breaking envelope changes require a new API versioned path.
- Legacy aliases may remain temporarily, but must advertise `deprecatedAlias = true` and the `canonicalPath` for migration.
- Consumers should treat unknown fields as forward-compatible additions.

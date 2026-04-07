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

## Current semantics

- `apiVersion = v1`
- `X-Discovery-Api-Version = v1`
- `X-Discovery-Schema-Family = discovery-api-envelope`
- `X-Discovery-Schema-Version = 1.0.0`

`schemaFamily` and `schemaVersion` in the **payload meta** describe the response payload contract.

- Public discovery query/write surfaces default to:
  - `schemaFamily = discovery-api-envelope`
  - `schemaVersion = 1.0.0`
- Discovery management exports may override these meta fields with payload-specific contracts such as:
  - `discovery.rebuild.summary`
  - `discovery.rollback.plan`
  - `discovery.platform.diagnostics`
  - `discovery.platform.probes`
  - `discovery.state.topology`

The response headers remain fixed to the shared envelope contract so clients can always detect the outer HTTP envelope shape independently of the payload-specific contract.

## Evolution policy

- Additive fields may be introduced inside `meta`, `data`, and `error.details` without changing the API version.
- Breaking envelope changes require a new API versioned path.
- Breaking payload-specific changes require a new payload schema family or payload schema version.
- Legacy aliases may remain temporarily, but must advertise `deprecatedAlias = true` and the `canonicalPath` for migration.
- Consumers should treat unknown fields as forward-compatible additions.

# Discovery Doctrine entities

This directory contains Discovery-owned Doctrine persistence models.

## Current persisted tables

- `discovery_feedback`
- `discovery_index_alias`
- `discovery_index_document`
- `discovery_operation_event_log`
- `discovery_rate_limit_bucket`
- `discovery_rebuild_evidence`
- `discovery_libsource_operator_event_log`

## Rules

- Every table name must start with `discovery_`.
- Class names must keep the `Entity` suffix.
- Doctrine entities should be backed by migration/schema baseline records.
- Runtime repositories/services may use these entities, but file-backed source repositories are not Doctrine repositories.

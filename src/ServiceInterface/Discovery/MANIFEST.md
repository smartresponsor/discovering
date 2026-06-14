# Discovering service contract mirror

This directory contains Discovering service contracts that are intentionally exposed as wiring or collaborator boundaries.

## Current mirror buckets

- `Adapter/`
- `Diagnostics/`
- `Document/`
- `Indexer/`
- `Libsource/`
- `Operations/`
- `Overview/`
- `RateLimit/`
- `Rebuild/`
- `Source/`
- `Support/`

## Current root-level contracts

- `DiscoveryFeedbackStoreInterface`
- `DiscoveryServiceInterface`

## Rules

- Root-level contracts are reserved for core discovery facade/store contracts.
- Bucket-level contracts should mirror an existing `src/Service/Discovery/<Bucket>/` capability.
- Contracts may be nested when the service capability is nested, for example `Source/Repository`.
- `Briefing`, `Playbook`, `Http`, `Rollback` and `Topology` currently do not require service-interface mirror buckets.

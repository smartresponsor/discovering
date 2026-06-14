# Discovering service capability taxonomy

This directory contains Discovering service-layer classes.

## Capability buckets

- `Adapter/` — search backend adapters.
- `Briefing/` — briefing management surface services.
- `Diagnostics/` — reachability/platform diagnostics.
- `Document/` — document creation/provider services.
- `Http/` — HTTP response and request surface services.
- `Indexer/` — indexing orchestration.
- `Libsource/` — libsource diagnostics, management actions and event logs.
- `Operations/` — operation logging and operation-event stores.
- `Overview/` — management overview service.
- `Playbook/` — playbook management surface services.
- `RateLimit/` — rate-limit stores and limiter.
- `Rebuild/` — rebuild evidence and staged-index planning.
- `Rollback/` — rollback execution.
- `Source/` — discovery source providers and source-record repositories.
- `Support/` — shared management surface support services.
- `Topology/` — state topology builders.

## Current root-level services

The current root-level service classes are accepted as transitional core discovery services:

- `ConfigurableDiscoveryFeedbackStore`
- `DiscoveryHighlightingService`
- `DiscoveryLearningService`
- `DiscoveryModePresetService`
- `DiscoveryScoringService`
- `DiscoveryService`
- `DoctrineDiscoveryFeedbackStore`

Future cleanup may move some of these into dedicated buckets, but Wave 18 only documents and checks the current posture.

## Rules

- New capability-specific services should prefer an existing capability bucket.
- New service interfaces must go to `src/ServiceInterface/Discovery/...`.
- Do not add broad mixed buckets such as `Manager/` or `Common/`.
- Root-level service classes should remain limited to core discovery orchestration/facade/scoring/feedback services.

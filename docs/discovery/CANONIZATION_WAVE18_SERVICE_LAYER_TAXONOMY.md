# Discovering Canonization Wave 18 — Service Layer Taxonomy

Wave 18 documents and machine-checks the Discovering service-layer posture without moving classes.

## Scope

Touched files:

- `src/Service/MANIFEST.md`
- `src/Service/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE18_SERVICE_LAYER_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

`src/Service/Discovery/` currently contains sixteen capability buckets plus seven accepted transitional root-level service classes.

The service layer is large, but it is not empty or arbitrary. Most classes already have capability-specific placement and type-identifiable suffixes.

## Canonical posture

Allowed current buckets:

- `Adapter`
- `Briefing`
- `Diagnostics`
- `Document`
- `Http`
- `Indexer`
- `Libsource`
- `Operations`
- `Overview`
- `Playbook`
- `RateLimit`
- `Rebuild`
- `Rollback`
- `Source`
- `Support`
- `Topology`

Accepted transitional root-level service files:

- `DiscoveryConfigurableFeedbackStore.php`
- `DiscoveryHighlightingService.php`
- `DiscoveryLearningService.php`
- `DiscoveryModePresetService.php`
- `DiscoveryScoringService.php`
- `DiscoveryService.php`
- `DiscoveryDoctrineFeedbackStore.php`

## Non-goals

- No service moves.
- No namespace rewrites.
- No interface extraction.
- No config rewiring.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected service taxonomy counter:

```text
wave18 service layer taxonomy findings: 0
```

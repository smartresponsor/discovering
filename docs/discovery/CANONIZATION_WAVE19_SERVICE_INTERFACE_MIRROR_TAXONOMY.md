# Discovering Canonization Wave 19 — ServiceInterface Mirror Taxonomy

Wave 19 documents and machine-checks the `src/ServiceInterface` mirror layer.

## Scope

Touched files:

- `src/ServiceInterface/MANIFEST.md`
- `src/ServiceInterface/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE19_SERVICE_INTERFACE_MIRROR_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

`src/ServiceInterface/Discovery` currently contains fifteen interfaces.

The mirror is intentionally selective. Not every implementation in `src/Service/Discovery` requires an interface. Contracts are used for facade/store/provider/adapter boundaries and runtime wiring seams.

## Canonical posture

Allowed current contract mirror buckets:

- `Adapter`
- `Diagnostics`
- `Document`
- `Indexer`
- `Libsource`
- `Operations`
- `Overview`
- `RateLimit`
- `Rebuild`
- `Source`
- `Support`

Allowed current root-level contracts:

- `DiscoveryFeedbackStoreInterface.php`
- `DiscoveryServiceInterface.php`

## Non-goals

- No new interface extraction.
- No implementation moves.
- No service alias rewiring.
- No namespace changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected service-interface mirror counter:

```text
wave19 service interface mirror taxonomy findings: 0
```

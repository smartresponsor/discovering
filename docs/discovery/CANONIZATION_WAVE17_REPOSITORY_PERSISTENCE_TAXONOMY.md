# Discovering Canonization Wave 17 — Repository / Persistence Taxonomy

Wave 17 documents and machine-checks the current persistence posture without changing storage behavior.

## Scope

Touched files:

- `src/Entity/MANIFEST.md`
- `src/Entity/Discovery/MANIFEST.md`
- `src/Service/Discovery/Source/Repository/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE17_REPOSITORY_PERSISTENCE_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

- Doctrine entities exist under `src/Entity/Discovery/`.
- All current Doctrine table names use the `discovery_` prefix.
- `src/Repository/` is not currently present.
- Source-record repositories live under `src/Service/Discovery/Source/Repository/` and are file-backed/source-backed service-layer classes, not Doctrine repositories.
- Wave 6 added a migration/schema baseline but did not enable Doctrine Migrations runtime dependencies.

## Canonical posture

`Repository` naming is currently allowed in `src/Service/Discovery/Source/Repository/` because those classes are source-record collection access services.

A future Doctrine repository wave may introduce `src/Repository/Discovery/`, but it should not mix file-backed source repositories with Doctrine persistence repositories.

## Non-goals

- No Doctrine repository introduction.
- No Entity mapping changes.
- No migration execution.
- No runtime dependency changes.
- No source repository moves.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected persistence taxonomy counter:

```text
wave17 repository persistence taxonomy findings: 0
```

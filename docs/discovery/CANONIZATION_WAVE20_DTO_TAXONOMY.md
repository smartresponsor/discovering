# Discovering Canonization Wave 20 — DTO Taxonomy

Wave 20 documents and machine-checks the DTO layer after the Wave 4 immutable DTO/value-object cleanup.

## Scope

Touched files:

- `src/Dto/MANIFEST.md`
- `src/Dto/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE20_DTO_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

`src/Dto/Discovery` currently contains 39 PHP DTO classes.

The DTO classes are already readonly after Wave 4, so Wave 20 does not rewrite DTO files. It establishes the audit boundary to prevent DTO drift.

## Canonical posture

DTOs are immutable payload objects for:

- public discovery queries/results,
- management surfaces,
- diagnostics,
- rebuild/rollback summaries,
- operation/operator events,
- topology snapshots.

The ecosystem does not require a `Dto` suffix when the class name clearly describes the payload role.

## Non-goals

- No DTO file rewrites.
- No DTO renaming.
- No namespace moves.
- No controller/form/service behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected DTO taxonomy counter:

```text
wave20 dto taxonomy findings: 0
```

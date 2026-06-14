# Discovering Canonization Wave 21 — ValueObject Taxonomy

Wave 21 documents and machine-checks the small Discovering value-object layer.

## Scope

Touched files:

- `src/ValueObject/MANIFEST.md`
- `src/ValueObject/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE21_VALUE_OBJECT_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

`src/ValueObject/Discovery` currently contains two readonly classes:

- `DiscoveryDocument`
- `DiscoveryScope`

## Canonical posture

Value objects represent semantic values and invariants. DTOs represent payload/transport surfaces. Doctrine entities represent persistence models.

## Non-goals

- No value-object rewrites.
- No class renames.
- No namespace moves.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected value-object taxonomy counter:

```text
wave21 value object taxonomy findings: 0
```

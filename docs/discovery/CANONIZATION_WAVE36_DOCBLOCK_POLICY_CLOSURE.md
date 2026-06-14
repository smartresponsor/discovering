# Discovering Canonization Wave 36 — Docblock Policy Closure

Wave 36 closes the remaining repository-local docblock policy failures found by `tools/docblock_policy_check.php`.

## Scope

Touched files:

- Doctrine entity classes missing class-level semantic docblocks.
- Doctrine-backed store classes missing class-level semantic docblocks.
- Doctrine entity-manager test factory missing class-level semantic docblock.
- `docs/discovery/CANONIZATION_WAVE36_DOCBLOCK_POLICY_CLOSURE.md`
- `tools/discovering_canon_audit.php`

## Functional posture

This wave is documentation-only at PHP runtime level.

It adds semantic class-level docblocks and does not change:

- class names,
- namespaces,
- attributes,
- method signatures,
- constructor signatures,
- service wiring,
- Doctrine mapping,
- tests.

## Verification

Run:

```bash
php tools/docblock_policy_check.php
php tools/discovering_canon_audit.php
```

Expected docblock policy counter:

```text
wave36 docblock policy findings: 0
```

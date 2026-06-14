# Discovering Canonization Wave 39 — Structural Closure Evidence

Wave 39 adds a local evidence generator for the structural canonicalization closure.

## Scope

Touched files:

- `tools/structural_closure_evidence.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/CANONIZATION_WAVE39_STRUCTURAL_CLOSURE_EVIDENCE.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New direct entrypoint:

```bash
php tools/structural_closure_evidence.php
```

Default output:

```text
var/discovery/evidence/structural_closure_evidence.json
```

Composer alias:

```bash
composer verify:structural-closure
```

The evidence report records:

- `runtime_preflight`
- `discovering_canon_audit --format=json`
- `local_ci`

## Non-goals

- No Composer dependency changes.
- No vendor generation.
- No runtime/container proof claim.
- No destructive cleanup.

## Verification

Run:

```bash
php -l tools/structural_closure_evidence.php
php tools/structural_closure_evidence.php
php tools/discovering_canon_audit.php
```

Expected evidence counter:

```text
wave39 structural closure evidence findings: 0
```

# Discovering Canonization Wave 42 — Evidence Index / Gate Registry

Wave 42 adds a repository-local evidence index for generated proof artifacts.

## Scope

Touched files:

- `tools/evidence_index.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/EVIDENCE_INDEX.md`
- `docs/discovery/CANONIZATION_WAVE42_EVIDENCE_INDEX_GATE_REGISTRY.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New command:

```bash
php tools/evidence_index.php
```

Default output:

```text
var/discovery/evidence/evidence_index.json
```

Composer alias:

```bash
composer verify:evidence-index
```

The index summarizes:

- `structural_closure_evidence.json`
- `runtime_dependency_evidence.json`
- `console_container_evidence.json`

## Non-goals

- No Composer dependency changes.
- No vendor generation.
- No runtime proof claim.
- No destructive cleanup.

## Verification

Run:

```bash
php -l tools/evidence_index.php
php tools/discovering_canon_audit.php
php tools/structural_closure_evidence.php
php tools/evidence_index.php
```

Expected evidence index counter:

```text
wave42 evidence index findings: 0
```

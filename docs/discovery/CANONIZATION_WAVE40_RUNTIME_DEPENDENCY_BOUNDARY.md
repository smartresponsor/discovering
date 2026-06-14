# Discovering Canonization Wave 40 — Runtime Dependency Boundary

Wave 40 records and instruments the boundary between structural closure and strict runtime dependency proof.

## Scope

Touched files:

- `tools/runtime_dependency_evidence.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/RUNTIME_PROOF_BOUNDARY.md`
- `docs/discovery/CANONIZATION_WAVE40_RUNTIME_DEPENDENCY_BOUNDARY.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New command:

```bash
php tools/runtime_dependency_evidence.php
```

Default output:

```text
var/discovery/evidence/runtime_dependency_evidence.json
```

Composer alias:

```bash
composer verify:runtime-dependency-evidence
```

This evidence is allowed to fail on an under-provisioned local machine. It is explicitly a runtime/vendor/environment proof gate.

## Non-goals

- No dependency changes.
- No vendor generation.
- No PHP extension installation.
- No service/container rewiring.
- No runtime proof claim.

## Verification

Run:

```bash
php -l tools/runtime_dependency_evidence.php
php tools/discovering_canon_audit.php
php tools/structural_closure_evidence.php
```

Expected runtime dependency boundary counter:

```text
wave40 runtime dependency boundary findings: 0
```

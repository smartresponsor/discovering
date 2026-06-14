# Discovering Canonization Wave 43 — Provisioned Runtime Checklist

Wave 43 adds a provisioned-machine checklist and PowerShell evidence runner for strict runtime/container proof.

## Scope

Touched files:

- `docs/discovery/PROVISIONED_RUNTIME_CHECKLIST.md`
- `docs/discovery/CANONIZATION_WAVE43_PROVISIONED_RUNTIME_CHECKLIST.md`
- `tools/provisioned_runtime_evidence.ps1`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New Windows runner:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot .
```

It runs:

- `tools/structural_closure_evidence.php`
- `tools/runtime_dependency_evidence.php`
- `tools/console_container_evidence.php`
- `tools/evidence_index.php`

## Non-goals

- No Composer dependency changes.
- No vendor generation in patch.
- No PHP extension installation in patch.
- No service/container rewiring.
- No destructive cleanup.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected provisioned runtime checklist counter:

```text
wave43 provisioned runtime checklist findings: 0
```

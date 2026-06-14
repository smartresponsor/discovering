# Discovering Canonization Wave 44 — Provisioned Runtime Runner Hardening

Wave 44 hardens the Windows provisioned runtime runner.

## Scope

Touched files:

- `tools/provisioned_runtime_evidence.ps1`
- `docs/discovery/PROVISIONED_RUNTIME_CHECKLIST.md`
- `docs/discovery/CANONIZATION_WAVE44_PROVISIONED_RUNTIME_RUNNER_HARDENING.md`
- `tools/discovering_canon_audit.php`

## Functional change

The runner now supports:

```powershell
-ContinueOnFailure
```

This mode keeps collecting evidence and attempts to write `evidence_index.json` even when runtime dependency or console/container evidence fails.

Strict behavior remains the default:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot .
```

Partial evidence mode:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot . -ContinueOnFailure
```

## Non-goals

- No dependency installation.
- No vendor generation.
- No runtime/container rewiring.
- No destructive cleanup.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected provisioned runtime runner counter:

```text
wave44 provisioned runtime runner findings: 0
```

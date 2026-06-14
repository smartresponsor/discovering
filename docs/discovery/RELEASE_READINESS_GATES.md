# Discovering Release Readiness Gates

This document records the release/readiness gate taxonomy for Discovering.

## Current readiness class

Discovering is treated as an ecosystem RC candidate until runtime proof is completed in a provisioned environment.

## Required documentation gates

- `docs/discovery/RC_READINESS.md`
- `docs/discovery/SUPPORT_MATRIX.md`
- `docs/discovery/KNOWN_LIMITATIONS.md`
- `docs/discovery/SECURITY_POSTURE.md`
- `docs/discovery/OBSERVABILITY_POSTURE.md`

## Required local preflight gates

- `php tools/runtime_preflight.php`
- `php tools/runtime_preflight.php --check-runtime-extensions`
- `php tools/runtime_preflight.php --require-composer`
- `php tools/runtime_preflight.php --require-vendor`
- `php tools/security_preflight.php`
- `php tools/discovering_canon_audit.php`
- `php tools/local_ci.php`
- `php tools/structural_closure_evidence.php`
- `php tools/runtime_dependency_evidence.php`
- `php tools/console_container_evidence.php`
- `php tools/evidence_index.php`
- `php tools/evidence_summary.php`
- `php tools/evidence_bundle.php`

## Required Composer script gates

- `composer ci:local`
- `composer ci:local:direct`
- `composer ci:runtime`
- `composer ci:quality`
- `composer ci`
- `composer validate --strict`
- `composer test`
- `composer test:all`
- `composer verify:runtime-preflight`
- `composer verify:runtime-extensions`
- `composer verify:composer-runtime`
- `composer verify:vendor-runtime`
- `composer verify:test-runtime`
- `composer verify:security`
- `composer verify:docblocks`
- `composer verify:structural-closure`
- `composer verify:runtime-dependency-evidence`
- `composer verify:console-container-evidence`
- `composer verify:evidence-index`
- `composer verify:evidence-summary`
- `composer verify:evidence-bundle`
- `composer lint:php`
- `composer analyse`
- `composer lint:cs`

## Runtime proof separation

This document does not claim runtime proof has been completed. It defines the gate surface that must be executed during the separate runtime proof phase.

## Non-goals

- No Composer dependency changes.
- No CI provider lock-in.
- No Kubernetes/deployment target requirement.
- No release promotion without actual runtime execution.


## Provisioned machine runner

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot .
```


## Evidence handoff

Review:

```text
docs/discovery/EVIDENCE_HANDOFF.md
```

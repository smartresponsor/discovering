# Discovering Evidence Handoff

This document explains how to generate and read Discovering evidence artifacts for review, PR handoff or release-readiness discussion.

## Structural closure handoff

Run:

```bash
php tools/structural_closure_evidence.php
php tools/evidence_index.php
php tools/evidence_summary.php
php tools/evidence_bundle.php
```

Expected structural status:

```text
structural_closure: PASS
```

The strict runtime boundaries may remain `FAIL` or `missing` on a non-provisioned machine.

## Provisioned runtime handoff

On a machine with PHP extensions, Composer and vendor installed, run:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot .
```

For partial evidence on an under-provisioned machine, run:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot . -ContinueOnFailure
```

## Primary artifacts

Generated under:

```text
var/discovery/evidence/
```

Important files:

- `structural_closure_evidence.json`
- `runtime_dependency_evidence.json`
- `console_container_evidence.json`
- `evidence_index.json`
- `evidence_summary.md`
- `discovering_evidence_bundle.zip`

## Reading the result

`evidence_summary.md` is the human-readable entry point.

`discovering_evidence_bundle.zip` is the handoff package.

`bundle_manifest.json` inside the ZIP lists which evidence files were present at export time.

## Interpretation rules

- `structural_closure: PASS` means repository taxonomy/structure gates are green.
- `runtime_dependency: FAIL` means the machine/runtime is not fully provisioned, or strict runtime dependencies are missing.
- `console_container: FAIL` means Symfony console/container proof did not pass in the current environment.
- Runtime/container failures do not invalidate structural closure.
- Runtime/container PASS must be claimed only from a provisioned machine.


## Retention policy

Before attaching or regenerating artifacts, review:

```text
docs/discovery/EVIDENCE_ARTIFACT_RETENTION.md
```

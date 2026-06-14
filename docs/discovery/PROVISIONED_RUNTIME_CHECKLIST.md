# Discovering Provisioned Runtime Checklist

This checklist is for a machine where strict runtime proof is expected to pass.

## Prerequisites

- PHP `>= 8.4`
- Composer v2 in `PATH`
- PHP extensions:
  - `json`
  - `pdo`
  - `pdo_sqlite`
  - `dom`
  - `mbstring`
  - `xml`
  - `xmlwriter`
- Dependencies installed through:

```bash
composer install
```

## Structural proof

```bash
php tools/structural_closure_evidence.php
```

Expected:

```text
Result: PASS
```

## Runtime dependency proof

```bash
php tools/runtime_dependency_evidence.php
```

Expected:

```text
Result: PASS
```

## Console/container proof

```bash
php tools/console_container_evidence.php
```

Expected:

```text
Result: PASS
```

## Evidence index

```bash
php tools/evidence_index.php
```

Expected after all boundaries pass:

```text
structural_closure: PASS
runtime_dependency: PASS
console_container: PASS
Result: PASS
```

## Windows PowerShell runner

Use:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot .
```

The script runs the three evidence generators and then writes the evidence index.


## Capturing partial evidence on under-provisioned machines

Use `-ContinueOnFailure` to generate as much evidence as possible even when strict runtime gates fail:

```powershell
powershell -ExecutionPolicy Bypass -File tools/provisioned_runtime_evidence.ps1 -ProjectRoot . -ContinueOnFailure
```

The command still exits non-zero when any strict gate fails, but it attempts to write the final evidence index.

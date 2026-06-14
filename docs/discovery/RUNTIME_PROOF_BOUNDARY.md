# Discovering Runtime Proof Boundary

Discovering has two separate readiness boundaries.

## Structural closure boundary

Structural closure proves repository shape and taxonomy.

Primary commands:

```bash
php tools/runtime_preflight.php
php tools/discovering_canon_audit.php
php tools/local_ci.php
php tools/structural_closure_evidence.php
```

Expected structural evidence:

```text
var/discovery/evidence/structural_closure_evidence.json
```

## Runtime dependency boundary

Runtime dependency proof verifies machine/environment readiness.

Primary commands:

```bash
php tools/runtime_preflight.php --check-runtime-extensions
php tools/runtime_preflight.php --require-composer
php tools/runtime_preflight.php --require-vendor
php tools/runtime_preflight.php --test-runtime
php tools/runtime_dependency_evidence.php
```

Expected runtime dependency evidence:

```text
var/discovery/evidence/runtime_dependency_evidence.json
```

## Why these are separate

A repository can be structurally complete while a local machine still lacks Composer, vendor dependencies or PHP extensions.

Structural closure should not be blocked by local machine provisioning.

Runtime proof should not be claimed until the strict runtime dependency boundary passes.

## Current strict dependencies

Application runtime requires:

- PHP `>= 8.4`
- `json`
- `pdo`
- `pdo_sqlite`
- Composer v2
- `vendor/autoload.php`

Test/QA runtime additionally requires:

- `dom`
- `mbstring`
- `xml`
- `xmlwriter`
- PHPUnit
- PHPStan
- PHP CS Fixer


## Console/container proof boundary

Console/container proof verifies that the installed vendor set can boot the Symfony console and container.

Primary commands:

```bash
php bin/console list --raw
php bin/console cache:clear --env=dev --no-warmup
php bin/console lint:container --env=dev
php tools/console_container_evidence.php
```

Expected console/container evidence:

```text
var/discovery/evidence/console_container_evidence.json
```

This boundary requires `vendor/autoload.php` and should be run only after dependency installation.

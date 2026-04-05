# Discovering Installability Proving v1

This document defines the minimal installability proof for the Discovering repository.

## Goal

The repository should prove that it is not only structurally Symfony-oriented, but also installable and bootable in a reproducible environment.

## Preflight phases

### Phase 1 — environment preflight

Run before dependency installation:

```bash
php tools/runtime_preflight.php
```

This checks:
- PHP version
- required PHP extensions
- Composer binary availability
- required repository entrypoints and config files

### Phase 2 — post-install preflight

Run after `composer install`:

```bash
php tools/runtime_preflight.php --require-vendor
```

This additionally checks:
- `vendor/autoload.php`

## Minimal proving sequence

```bash
composer validate --strict
composer install --prefer-dist --no-interaction --no-progress
php tools/runtime_preflight.php --require-vendor
php bin/console list --raw
composer test:unit
composer test:contract
composer test:behavioral
composer test:functional
```

## Failure classes

Typical direct blockers are:
- Composer missing from the environment
- `pdo_sqlite` missing even though Discovering currently requires SQLite-backed local discovery and feedback storage
- `vendor/autoload.php` missing because dependencies were not installed
- framework boot failures surfacing in `php bin/console list --raw`

## Scope note

This proving baseline does not claim production readiness by itself.
It only proves:
- installability
- entrypoint bootability
- baseline test contour execution

Deployment, staging cutover, and operational SLO evidence are covered by separate documents.

# Discovering installability proof — 2026-04-03

## Scope

This report records the direct installability proving attempt from the current local session.

## Repository-side additions in this wave

- repository-local runtime preflight script: `tools/runtime_preflight.php`
- installability proving document: `docs/discovery/INSTALLABILITY_PROVING_V1.md`
- CI workflow upgraded with pre-install and post-install preflight plus console boot smoke
- Composer scripts upgraded with explicit runtime verification entrypoints

## Direct result in the current container

Environment preflight was executed with:

```bash
php tools/runtime_preflight.php
```

Observed blockers:

1. `pdo_sqlite` is missing in the current container.
2. Composer binary is missing in the current container.

Because of those two direct blockers, the following proving steps could not be executed in this container:

- `composer install`
- `php bin/console list --raw`
- PHPUnit suites with vendor-installed dependencies

## Interpretation

This is not a repository-structure failure.
The current verdict is:

- repository baseline for installability proving now exists
- current execution environment remains incomplete for full proof

## Next proving target

Run the following sequence in an environment that has Composer and `pdo_sqlite` available:

```bash
composer install --prefer-dist --no-interaction --no-progress
php tools/runtime_preflight.php --require-vendor
php bin/console list --raw
composer test:unit
composer test:contract
composer test:behavioral
composer test:functional
```

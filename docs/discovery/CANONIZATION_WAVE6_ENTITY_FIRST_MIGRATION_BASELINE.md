# Discovering Canonization Wave 6: Entity-first Migration Baseline

Date: 2026-05-01
Base: current Discovering slice plus waves 1-5.
Scope: migration-ready baseline only; no runtime bundle activation and no broad repository rewrite.

## Why this wave exists

The current repository already has Doctrine entities under `src/Entity/Discovery` and every mapped table uses the required `discovery_` prefix. The missing part is a durable migration baseline. Without a baseline, a host application can see mapped entities but still lacks a canonical, reviewable schema artifact for provisioning or promotion.

## What changed

- Added `migrations/Version20260501032000.php` as the initial Doctrine migration class for the currently mapped Discovering tables.
- Added `migrations/discovery_schema_baseline.sql` as a plain SQL mirror of the same baseline for review and non-bundle verification.
- Updated the static audit tool so it scans migrations recursively and no longer treats the repository as migration-less once this baseline is present.

## What intentionally did not change

- `composer.json` was not changed.
- `config/bundles.php` was not changed.
- `config/packages/doctrine_migrations.yaml` was not added.
- Doctrine Migrations Bundle was not enabled.
- Entity classes were not moved or renamed.
- Existing file/ephemeral stores were not deleted.

This is deliberate. The current runtime dependencies do not declare Doctrine Migrations. Enabling migration config before the package is installed would risk breaking `php bin/console lint:container`. This wave therefore establishes the schema baseline without forcing runtime activation.

## Tables covered

- `discovery_feedback`
- `discovery_index_alias`
- `discovery_index_document`
- `discovery_operation_event_log`
- `discovery_rate_limit_bucket`
- `discovery_rebuild_evidence`
- `discovery_libsource_operator_event_log`

## Entity-first status after this wave

Entity-first posture becomes stronger but not complete:

- Doctrine entities exist.
- All current tables are discovery-prefixed.
- A migration-ready baseline exists.
- Runtime migration execution still requires an explicit future wave that adds Doctrine Migrations dependency/config.
- Repository/service wiring still needs a later pass to make Doctrine the clearly primary persistence path where business-appropriate.

## Recommended checks

```bash
php -l migrations/Version20260501032000.php
php tools/discovering_canon_audit.php
php bin/console lint:container
```

`php bin/console doctrine:migrations:migrate` is intentionally not part of this wave because the migrations bundle is not yet enabled.

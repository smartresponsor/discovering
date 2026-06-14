# Discovering Canonization Wave 7: Root Manifest Taxonomy

## Purpose

Wave 7 reduces repository-root clutter without changing runtime behavior. The current slice had several producer-facing manifest files at repository root plus stale patch-delivery manifests from older delivery waves. That made the root look less Symfony-oriented and made it harder to distinguish durable project guidance from one-off patch metadata.

## Scope

This wave moves durable producer-facing manifest material into `docs/manifests/`:

- `ARCHITECTURE_MANIFEST.md` -> `docs/manifests/ARCHITECTURE_MANIFEST.md`
- `BOUNDING_MANIFEST.md` -> `docs/manifests/BOUNDING_MANIFEST.md`
- `PRODUCT_MANIFEST.md` -> `docs/manifests/PRODUCT_MANIFEST.md`
- `CODEX_CLI_PROMPT.txt` -> `docs/manifests/CODEX_CLI_PROMPT.txt`

This wave retires stale root-level patch-delivery artifacts when present:

- `MANIFEST.txt`
- `PATCH_MANIFEST.txt`

The apply script backs up retired root files under `.patch-backups/discovering_wave7_root_manifest_taxonomy/` before removal.

## Non-goals

- No production PHP source is moved.
- No Entity, migration, service, subscriber, DTO, form, or test runtime code is changed.
- No destructive repository overwrite is performed.
- No generated documentation or report history is deleted.

## Canonical direction

The repository root should stay focused on standard Symfony and project entry points:

- `README.md`
- `composer.json` / `composer.lock`
- `.env*`
- `phpunit.xml.dist`
- `phpstan.neon.dist`
- `.php-cs-fixer*`
- `bin/`, `config/`, `src/`, `tests/`, `tools/`, `docs/`, `migrations/`, `resources/`, `templates/`, `report/`

Durable narrative/project manifest material belongs in `docs/manifests/` or `docs/discovery/` depending on audience. One-off patch metadata should live only inside delivered patch archives or backup folders, not as permanent root files.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

The audit reports a `root_manifest_files` count and flags producer/stale manifest files if they reappear at repository root.

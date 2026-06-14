# Discovering Canonization Wave 32 — Runtime Preflight Environment Split

Wave 32 separates repository-structure preflight from local-machine/dependency preflight.

## Scope

Touched files:

- `tools/runtime_preflight.php`
- `docs/discovery/CANONIZATION_WAVE32_RUNTIME_PREFLIGHT_ENVIRONMENT_SPLIT.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

Before Wave 32, plain:

```bash
php tools/runtime_preflight.php
```

failed when the local machine lacked Composer or `pdo_sqlite`, even when the repository structure itself was valid.

After Wave 32:

```bash
php tools/runtime_preflight.php
```

checks repository entrypoints and required files.

Stricter environment checks are explicit:

```bash
php tools/runtime_preflight.php --check-runtime-extensions
php tools/runtime_preflight.php --require-composer
php tools/runtime_preflight.php --require-vendor
php tools/runtime_preflight.php --test-runtime
```

`--require-vendor` implies runtime-extension checks and Composer availability.

## Non-goals

- No Composer dependency changes.
- No PHP extension installation.
- No vendor generation.
- No Symfony service/container changes.
- No release promotion.

## Verification

Run:

```bash
php -l tools/runtime_preflight.php
php tools/runtime_preflight.php
php tools/runtime_preflight.php --json
php tools/discovering_canon_audit.php
```

Expected runtime preflight taxonomy counter:

```text
wave32 runtime preflight environment split findings: 0
```

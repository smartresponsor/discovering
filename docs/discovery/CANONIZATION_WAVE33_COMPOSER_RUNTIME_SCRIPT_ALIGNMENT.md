# Discovering Canonization Wave 33 — Composer Runtime Script Alignment

Wave 33 aligns Composer scripts with the Wave 32 runtime-preflight environment split.

## Scope

Touched files:

- `composer.json`
- `docs/discovery/CANONIZATION_WAVE33_COMPOSER_RUNTIME_SCRIPT_ALIGNMENT.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

Before Wave 33:

```bash
composer verify:runtime-preflight
```

executed:

```bash
php tools/runtime_preflight.php --require-vendor
```

That mixed repository-structure checks with vendor/environment checks.

After Wave 33:

```bash
composer verify:runtime-preflight
```

executes the structural repository preflight:

```bash
php tools/runtime_preflight.php
```

Stricter checks are explicit:

```bash
composer verify:runtime-extensions
composer verify:composer-runtime
composer verify:vendor-runtime
composer verify:test-runtime
```

## Non-goals

- No dependency changes.
- No vendor generation.
- No PHPUnit/PHPStan execution.
- No release promotion.

## Verification

Run:

```bash
composer validate --strict
php tools/discovering_canon_audit.php
php tools/runtime_preflight.php
```

Expected Composer runtime script counter:

```text
wave33 composer runtime script findings: 0
```

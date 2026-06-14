# Discovering Canonization Wave 34 — CI Script Profile Split

Wave 34 splits Composer CI scripts into structural, runtime and quality profiles.

## Scope

Touched files:

- `composer.json`
- `docs/discovery/CANONIZATION_WAVE34_CI_SCRIPT_PROFILE_SPLIT.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

Before Wave 34, `composer ci` mixed local structural checks with vendor/runtime/test execution.

After Wave 34:

```bash
composer ci:local
```

runs repository-local structural gates that do not require vendor/test runtime.

```bash
composer ci:runtime
```

runs runtime/vendor/test gates.

```bash
composer ci:quality
```

runs PHPStan and CS checks through guarded tool wrappers.

```bash
composer ci
```

aggregates the three profiles.

## Non-goals

- No dependency changes.
- No vendor generation.
- No PHPUnit/PHPStan execution.
- No CI provider binding.

## Verification

Run:

```bash
composer validate --strict
php tools/discovering_canon_audit.php
php tools/runtime_preflight.php
```

Expected CI profile counter:

```text
wave34 ci script profile findings: 0
```

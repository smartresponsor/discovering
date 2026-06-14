# Discovering Canonization Wave 35 — Local CI Entrypoint

Wave 35 adds a repository-local structural CI wrapper that does not require Composer scripts or vendor autoload.

## Scope

Touched files:

- `tools/local_ci.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/CANONIZATION_WAVE35_LOCAL_CI_ENTRYPOINT.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New direct local CI entrypoint:

```bash
php tools/local_ci.php
```

It runs:

- `tools/runtime_preflight.php`
- `tools/security_preflight.php`
- `tools/discovering_canon_audit.php`
- `tools/lint_php.php`
- `tools/docblock_policy_check.php`

Composer alias:

```bash
composer ci:local:direct
```

## Non-goals

- No Composer dependency changes.
- No vendor generation.
- No PHPUnit/PHPStan/PHP-CS-Fixer execution.
- No runtime/container proof claim.

## Verification

Run:

```bash
php -l tools/local_ci.php
php tools/runtime_preflight.php
php tools/discovering_canon_audit.php
```

Expected local CI entrypoint counter:

```text
wave35 local ci entrypoint findings: 0
```

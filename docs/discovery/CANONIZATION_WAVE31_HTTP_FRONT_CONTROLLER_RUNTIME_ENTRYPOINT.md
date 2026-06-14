# Discovering Canonization Wave 31 — HTTP Front Controller Runtime Entrypoint

Wave 31 closes a concrete runtime preflight gap by adding the missing Symfony HTTP front controller.

## Scope

Touched files:

- `public/index.php`
- `docs/discovery/CANONIZATION_WAVE31_HTTP_FRONT_CONTROLLER_RUNTIME_ENTRYPOINT.md`
- `tools/discovering_canon_audit.php`

## Functional fix

`tools/runtime_preflight.php` expects `public/index.php` to exist as the HTTP entrypoint. The current slice had `src/Kernel.php` and `bin/console`, but no HTTP front controller.

Wave 31 adds a minimal Symfony-oriented front controller:

- loads Composer autoload,
- boots `.env` through Symfony Dotenv when available,
- enables Symfony Debug when `APP_DEBUG` is truthy,
- creates the HTTP request,
- handles and terminates the kernel.

## Non-goals

- No route changes.
- No service wiring changes.
- No dependency changes.
- No deployment target changes.
- No claim of full runtime proof.

## Verification

Run:

```bash
php -l public/index.php
php -l tools/discovering_canon_audit.php
php tools/discovering_canon_audit.php
php tools/runtime_preflight.php
```

Expected HTTP front-controller counter:

```text
wave31 http front controller findings: 0
```

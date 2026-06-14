# Discovering Canonization Wave 10 — Config Service Wiring Taxonomy

## Goal

Keep the root Symfony service configuration compact and move Discovering-specific wiring into a dedicated imported configuration file.

## Touched scope

- `config/services.yaml`
- `config/services/discovery.yaml`
- `tools/discovering_canon_audit.php`
- this wave note

## Canonical posture

`config/services.yaml` should describe the application-level service prototype only:

- import component-specific service configuration;
- keep shared `_defaults`;
- register `App\`;
- register controller service arguments.

Discovering-specific parameters, service aliases, tagged iterators, backend selectors, rate-limit settings, and store wiring belong in `config/services/discovery.yaml`.

## Non-goals

This wave does not add new runtime dependencies, does not enable Doctrine migrations, and does not move PHP classes.

## Verification

```bash
php -l tools/discovering_canon_audit.php
php tools/discovering_canon_audit.php
php bin/console lint:yaml config/services.yaml config/services/discovery.yaml
php bin/console lint:container
```

Expected audit posture after this wave:

```text
wave10 service config findings: 0
```

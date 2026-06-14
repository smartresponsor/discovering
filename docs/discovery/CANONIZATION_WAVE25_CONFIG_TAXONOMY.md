# Discovering Canonization Wave 25 — Config Taxonomy Hardening

Wave 25 documents and machine-checks Symfony config posture after service and route split waves.

## Scope

Touched files:

- `config/MANIFEST.md`
- `config/services/MANIFEST.md`
- `config/packages/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE25_CONFIG_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

- `config/services.yaml` imports `config/services/discovery.yaml`.
- Discovering service aliases and parameters live in `config/services/discovery.yaml`.
- `config/routes/discovery.yaml` aggregates public and management route files.
- Doctrine migrations baseline exists, but runtime migrations bundle/config has not been enabled.

## Canonical posture

Config taxonomy is split by responsibility:

- service wiring,
- routes,
- package/bundle config,
- test overrides.

## Non-goals

- No service wiring changes.
- No route changes.
- No package config changes.
- No migrations bundle activation.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected config taxonomy counter:

```text
wave25 config taxonomy findings: 0
```

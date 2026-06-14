# Discovering config taxonomy

Symfony configuration is organized by runtime responsibility.

## Canonical files and buckets

- `config/services.yaml` is the root services aggregator.
- `config/services/discovery.yaml` contains Discovering-specific service parameters, tags and aliases.
- `config/routes/discovery.yaml` is the Discovering route aggregator.
- `config/routes/discovery_public.yaml` contains public discovery routes.
- `config/routes/discovery_management.yaml` contains operator/management discovery routes.
- `config/packages/` contains Symfony bundle/package configuration.
- `config/bundles.php` contains registered Symfony bundles.

## Rules

- Component-specific service wiring should live under `config/services/discovery.yaml`, not be re-expanded into root `services.yaml`.
- Discovering route files should stay split by public and management surfaces.
- Package configuration should not be used for component service aliases.
- Doctrine migrations runtime config should be introduced only in a dependency/runtime wave, not in taxonomy waves.

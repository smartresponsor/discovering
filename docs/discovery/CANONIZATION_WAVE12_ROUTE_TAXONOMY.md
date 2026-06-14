# Canonization Wave 12: HTTP Route Taxonomy

Wave 12 separates public discovery routes from operator management routes while keeping the existing Symfony attribute controllers and route names intact.

## Scope

Touched files:

- `config/routes/discovery.yaml`
- `config/routes/discovery_public.yaml`
- `config/routes/discovery_management.yaml`
- `config/routes/MANIFEST.md`
- `tools/discovering_canon_audit.php`

## Intent

The previous `config/routes/discovery.yaml` mixed public discovery and management discovery controller imports. That works, but it makes the route surface less readable as the component grows.

This wave keeps `config/routes/discovery.yaml` as the aggregate file and moves the two route-surface imports into explicit files:

```text
config/routes/discovery.yaml
├── config/routes/discovery_public.yaml
└── config/routes/discovery_management.yaml
```

## Route naming posture

Public routes remain under `app_discovery_*`. Management routes remain under `app_management_discovery_*`. No URL path changes are introduced in this wave.

## Non-goals

This wave does not:

- remove legacy unversioned API routes,
- change controller namespaces,
- change API payloads,
- alter OpenAPI/Nelmio bundle registration,
- introduce runtime dependencies.

Legacy API compatibility routes such as `/api/discovery` and `/api/discovery/click` remain available alongside `/api/discovery` routes until a dedicated deprecation/removal wave.

## Verification

Run:

```bash
php -l tools/discovering_canon_audit.php
php tools/discovering_canon_audit.php
php bin/console lint:yaml config/routes/discovery.yaml config/routes/discovery_public.yaml config/routes/discovery_management.yaml
php bin/console debug:router | grep discovery
php bin/console lint:container
```

Expected route taxonomy counter:

```text
wave12 route taxonomy findings: 0
```

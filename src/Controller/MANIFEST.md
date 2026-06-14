# Discovering controller taxonomy

Discovering controllers are organized by HTTP interaction surface.

## Canonical buckets

- `src/Controller/Discovery/` contains public discovery controllers.
- `src/Controller/Management/` contains operator/management controllers.

## Rules

- Concrete controller classes must keep the `Controller` suffix.
- Public controllers should expose `app_discovery_*` route names and `/discovery` paths.
- Management controllers should expose `app_management_discovery_*` route names and `/management/discovery` paths.
- Controller classes should remain thin HTTP adapters and delegate business work to services.
- Shared management controller primitives may be abstract, but concrete public/management controllers must be final.

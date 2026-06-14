# Route Configuration Manifest

Discovery routes are split by HTTP surface instead of being grouped in one mixed route file.

## Files

- `discovery.yaml` is the route aggregate imported by Symfony.
- `discovery_public.yaml` imports public UI/API controllers from `src/Controller/Discovery/`.
- `discovery_management.yaml` imports operator/management controllers from `src/Controller/Management/`.
- `nelmio_api_doc.php` declares optional API documentation routes guarded by bundle availability.

## Naming policy

- Public discovery route names use `app_discovery_*`.
- Operator management route names use `app_management_discovery_*`.
- Versioned API routes keep explicit `*_v1` names while legacy unversioned API routes remain as compatibility entrypoints until a later BC-removal wave.

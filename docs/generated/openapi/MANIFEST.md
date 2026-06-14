# Discovering OpenAPI generated surface

This directory is reserved for generated OpenAPI artifacts.

## Current posture

Nelmio API Doc config defines separate Discovering API documentation areas:

- `public_discovery`
- `management_discovery`

The current directory is a generated-artifact destination, not a hand-written API narrative bucket.

## Rules

- Hand-written API explanation belongs under Antora pages or `docs/discovery/`.
- Generated OpenAPI files belong here.
- Nelmio route/config wiring belongs under `config/routes/nelmio_api_doc.php` and `config/packages/nelmio_api_doc.php`.
- API JSON responses should be shaped through `DiscoveryJsonResponseFactory`.

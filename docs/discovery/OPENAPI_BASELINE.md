# Discovering OpenAPI baseline

Discovering exposes two OpenAPI areas when `nelmio/api-doc-bundle` is installed in a dev/test environment:

- `public_discovery`
- `management_discovery`

## Intended routes

- `/api/doc/public-discovery`
- `/api/doc/public-discovery.json`
- `/api/doc/management-discovery`
- `/api/doc/management-discovery.json`

## Current posture

This repository now contains integration-ready Nelmio bundle wiring, routes, and composer requirements.
If the package is not installed in the working environment, the bundle stays conditionally disabled and runtime boot remains unaffected.

## Follow-up hardening

- add operation-level OpenAPI attributes or descriptive model metadata for all JSON endpoints;
- ensure public and management schemas remain versioned with the existing `X-Discovery-*` headers;
- export generated OpenAPI artifacts in CI once Composer-based dev dependencies are available.

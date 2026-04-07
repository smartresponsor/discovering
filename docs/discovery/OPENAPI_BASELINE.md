# OpenAPI Baseline

## Current posture

Discovering is prepared to expose an OpenAPI-compatible documentation surface through `NelmioApiDocBundle` without turning this repository into a standalone documentation portal.

## Areas

- `public_discovery` — public discovery JSON endpoints
- `management_discovery` — management and operator-facing HTTP endpoints

## Routes

When the Nelmio bundle is installed in a dev or test environment, the following routes should become available:

- `/api/doc/public-discovery`
- `/api/doc/public-discovery.json`
- `/api/doc/management-discovery`
- `/api/doc/management-discovery.json`

## Generated artifacts

Generated OpenAPI artifacts should be treated as generated documentation output rather than hand-written narrative documentation.

Recommended repository-local destination:

- `docs/generated/openapi/`

Narrative documentation should continue to live in `docs/discovery/`, with GitHub-facing repository material remaining in root-level Markdown files.


## Swagger UI endpoints

When `nelmio/api-doc-bundle` is installed in the active environment, the repository exposes:

- `/api/doc/public-discovery`
- `/api/doc/public-discovery.json`
- `/api/doc/management-discovery`
- `/api/doc/management-discovery.json`

These routes are integration-ready and intentionally scoped to the producer repository. Site assembly remains the responsibility of the external documentation aggregator.

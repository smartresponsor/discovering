# Discovering HTTP/API services

This directory contains HTTP/API support services.

## Current services

- `DiscoveryApiContract` — describes API contract posture.
- `DiscoveryJsonResponseFactory` — builds consistent JSON API responses.
- `DiscoveryRequestSurfacePolicy` — classifies/guards request surfaces.

## Rules

- JSON response envelope logic belongs in `DiscoveryJsonResponseFactory`.
- Request-surface authorization/hardening policy belongs in `DiscoveryRequestSurfacePolicy`.
- API contract metadata belongs in HTTP/API service classes or generated docs, not controllers.
- Controllers should remain thin HTTP adapters.

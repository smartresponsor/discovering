# Discovering Security Posture

This document records the current Discovering security surface.

## Request-surface policy

Request surface classification is owned by:

- `src/Service/Discovery/Http/DiscoveryRequestSurfacePolicy.php`

The policy separates:

- public query paths,
- API write paths,
- management paths,
- management mutation paths,
- JSON response surfaces.

## Subscribers

Security-related Symfony subscribers live under `src/Subscriber/Discovery/`:

- `DiscoveryEndpointSecuritySubscriber`
- `DiscoveryMutationRequestHardeningSubscriber`
- `DiscoveryRateLimitSubscriber`
- `DiscoveryRequestCorrelationSubscriber`
- `DiscoveryResponseSecurityHeadersSubscriber`

## Token posture

Management/API mutation protection is wired through environment variables in service configuration:

- `APP_DISCOVERY_MANAGEMENT_TOKEN`
- `APP_DISCOVERY_API_WRITE_TOKEN`

## Rate-limit posture

Rate-limit defaults are configured under `config/services/discovery.yaml` using `app.discovery.default_*` parameters and environment overrides.

Relevant service classes:

- `DiscoveryRateLimiter`
- `DiscoveryConfigurableRateLimitStore`
- `DiscoveryDoctrineRateLimitStore`
- `DiscoveryFileRateLimitStore`

## Generated documentation

OpenAPI/Nelmio documentation routes and configuration are present but optional/guarded by bundle availability.

## Verification

Run:

```bash
php tools/security_preflight.php
php tools/discovering_canon_audit.php
```

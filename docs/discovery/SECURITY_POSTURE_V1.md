# Discovery Security Posture v1

## Current posture summary
`Discovering` currently uses a lightweight but explicit security baseline appropriate for a local or early shared environment:

- token-gated management surfaces
- token-gated API write surface for feedback events
- scope-specific rate limiting for query, write, and management-mutation traffic
- public read-only discovery query path
- response correlation and operational logging
- response hardening headers across discovery HTTP surfaces
- contract and functional tests around authorization and HTTP envelopes

## Implemented controls

### Access control
- `/management/discovery*` requires `X-Discovery-Management-Token`
- `POST /api/discovery/click` and `POST /api/discovery/click` require `X-Discovery-Api-Write-Token`
- read-only discovery queries remain publicly callable by design

### Response hardening
All discovery responses currently emit:

- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: no-referrer`
- `X-Frame-Options: DENY`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'`
- `Cache-Control: no-store, private`

### Observability & auditability
- per-request correlation IDs via `X-Request-Id`
- discovery operation log export
- rebuild evidence export
- versioned API envelopes with explicit schema metadata

## Known gaps
- no per-user authentication or RBAC
- no token rotation workflow documented yet
- no dedicated CSRF strategy beyond token-gated write endpoints
- no formal secret backend integration
- no threat-based abuse detection metrics yet

## Intended deployment stance
This baseline is sufficient for:
- local development
- controlled operator usage
- early internal integration

Before broader shared deployment, add:
- secret rotation and storage procedure
- reverse proxy / TLS hardening runbook
- CI security smoke execution
- stronger mutation audit policy
- distributed-safe throttling if multi-replica deployment becomes a target

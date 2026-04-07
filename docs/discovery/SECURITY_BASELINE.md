# Security Baseline

## Current minimum baseline

Discovering should maintain a lightweight but explicit HTTP security baseline that fits a Symfony-oriented ecosystem component.

### Implemented controls

- request correlation ID propagation
- management token gate
- write-token gate for mutable API endpoints
- rate limiting for discovery surfaces
- response security headers on discovery and management paths

### Current limitations

This baseline is intentionally minimal and does not yet represent full production security hardening.

Remaining work before RC should include:

- stronger authentication posture for management endpoints
- clearer authorization model for operator-only actions
- OpenAPI-backed API visibility for security review
- CI enforcement for security preflight checks

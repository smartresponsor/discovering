# Discovering security baseline

## Current protections

- token-protected management mutation and export surfaces;
- token-protected API write surface for click/feedback ingestion;
- response security headers for discovery, API, and management paths;
- request correlation IDs through `X-Request-Id`;
- request rate limiting with scope-aware headers and retry metadata.

## Ecosystem RC minimum

- management requests must provide `X-Discovery-Management-Token`;
- API write requests must provide `X-Discovery-Api-Write-Token`;
- mutation endpoints should accept only explicit content types (`application/json`, `application/x-www-form-urlencoded`, or `multipart/form-data`);
- discovery JSON routes must emit `X-Discovery-Api-Version`, `X-Discovery-Schema-Family`, and `X-Discovery-Schema-Version`;
- discovery and management responses must remain non-cacheable.

## Remaining work

- move from shared static tokens toward stronger ecosystem auth/authz integration;
- introduce clearer RBAC for public, write, and management surfaces;
- formalize secret rotation expectations and deployment-time secret delivery.

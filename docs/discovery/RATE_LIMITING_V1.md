# Discovery Rate Limiting v1

## Scope
The current rate-limiting baseline protects three discovery traffic classes:

- `query` — public read paths (`/discovery`, `/api/discovery`, `/api/v1/discovery`)
- `write` — feedback-write paths (`/discovery/feedback`, `/api/discovery/click`, `/api/v1/discovery/click`)
- `management_mutation` — management mutation flows (for example `POST /management/discovery/rebuild` or management actions triggered with `?action=...`)

## Current implementation
The limiter uses a lightweight fixed-window file-backed store. This is intentionally modest but explicit:

- file-backed JSON bucket state
- per-scope counting
- actor bucket derived from client IP and token fingerprint where relevant
- explicit `429` handling
- response headers with reset metadata

## Headers
Discovery rate-limited surfaces now emit:

- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `X-RateLimit-Reset`
- `X-RateLimit-Scope`
- `Retry-After` when a request is rejected

## Environment overrides
Optional overrides:

- `APP_DISCOVERY_RATE_LIMIT_STORE_PATH`
- `APP_DISCOVERY_QUERY_RATE_LIMIT`
- `APP_DISCOVERY_QUERY_RATE_LIMIT_WINDOW_SECONDS`
- `APP_DISCOVERY_WRITE_RATE_LIMIT`
- `APP_DISCOVERY_WRITE_RATE_LIMIT_WINDOW_SECONDS`
- `APP_DISCOVERY_MANAGEMENT_MUTATION_RATE_LIMIT`
- `APP_DISCOVERY_MANAGEMENT_MUTATION_RATE_LIMIT_WINDOW_SECONDS`

## Operational caveat
This baseline is **not** treated as distributed-safe. The limiter state is file-backed and appropriate for local or single-node usage only. It reduces obvious abuse paths but does not yet provide multi-replica coordination.

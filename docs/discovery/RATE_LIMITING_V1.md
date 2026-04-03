# Discovery rate limiting v1

This document describes the current anti-abuse baseline for discovery query, write, and management mutation surfaces.

## Current scopes

- `query`
- `write`
- `management_mutation`

## Default backend

The default rate-limit store remains a local JSON file so the component stays lightweight in single-node development.

## Shared coordination seam

A stronger coordination seam now exists for throttling through a PDO-backed store.

Environment knobs:

- `APP_DISCOVERY_RATE_LIMIT_BACKEND=file|pdo`
- `APP_DISCOVERY_RATE_LIMIT_PDO_DSN=`
- `APP_DISCOVERY_RATE_LIMIT_PDO_USER=`
- `APP_DISCOVERY_RATE_LIMIT_PDO_PASSWORD=`
- `APP_DISCOVERY_RATE_LIMIT_PDO_TABLE=`

When `APP_DISCOVERY_RATE_LIMIT_BACKEND=pdo`, rate limiting no longer depends on a JSON file and can coordinate counters through a shared database table.

## Important limits of this wave

This wave improves anti-abuse coordination for throttling only. Discovery index state, feedback, rebuild evidence, and operator logs still remain SQLite/JSON-file oriented, so overall distributed readiness is still not claimed.

# Discovery Threat Model v1

## Scope
This document covers the current `Discovering` HTTP surfaces and operator flows:

- public discovery UI: `/discovery`
- public discovery API: `/api/discovery`, `/api/v1/discovery`
- discovery feedback write API: `/api/discovery/click`, `/api/v1/discovery/click`
- discovery management surfaces: `/management/discovery*`
- local/operator rebuild and export flows

## Protected assets
- discovery source records and indexed document corpus
- feedback-learning signal integrity
- management exports and rebuild evidence history
- management and API write tokens
- operator event logs and operational metadata

## Primary threat scenarios

### 1. Unauthorized management access
**Threat:** a caller reaches `/management/discovery*` without operator authorization.

**Impact:** disclosure of management summaries, rebuild evidence, operations history, or destructive operator actions in future waves.

**Current control:** dedicated management token header (`X-Discovery-Management-Token`).

**Residual risk:** token leakage through logs, local shell history, or misconfigured reverse proxy.

### 2. Feedback poisoning / click inflation
**Threat:** a caller submits repeated discovery click events to distort ranking.

**Impact:** relevance drift and reduced trust in feedback learning.

**Current control:** dedicated write token header (`X-Discovery-Api-Write-Token`), clear separation from read-only query paths, and scope-specific rate limiting.

**Residual risk:** valid token holders can still submit bursts within the configured window unless later dedup heuristics are added.

### 3. Sensitive response caching
**Threat:** browser or intermediary caches management exports or evolving search responses.

**Impact:** stale or leaked operational data.

**Current control:** discovery responses are emitted with `Cache-Control: no-store, private`.

**Residual risk:** upstream infrastructure may still override headers if misconfigured.

### 4. UI embedding / clickjacking
**Threat:** discovery or management screens are framed by another origin.

**Impact:** operator or user interaction hijacking.

**Current control:** `X-Frame-Options: DENY` and `Content-Security-Policy: frame-ancestors 'none'`.

### 5. Browser capability overreach
**Threat:** discovery pages run with unnecessary browser permissions.

**Impact:** larger client-side attack surface than needed.

**Current control:** `Permissions-Policy` denies camera, microphone, and geolocation.

### 6. Content-type confusion
**Threat:** a client or intermediary interprets discovery responses as a different content type.

**Impact:** increased XSS or file interpretation risk.

**Current control:** `X-Content-Type-Options: nosniff`.

## Out-of-scope for current slice
- OAuth/SSO
- per-user RBAC
- encrypted secret backends / vault integration
- external search backend network segmentation
- end-to-end reverse proxy / TLS posture

## Next security priorities
1. add replay-resistant write protection or click dedup heuristics
2. move management and write tokens to stronger operational secret handling
3. add security smoke checks into CI
4. add destructive-action confirmation and audit expansion for management mutations
5. replace local file-backed throttling with a stronger distributed coordination store if multi-replica deployment becomes a target

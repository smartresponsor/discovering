# Discovering Canonization Wave 28 — Security Posture Hardening

Wave 28 documents and machine-checks the Discovering security posture.

## Scope

Touched files:

- `docs/discovery/SECURITY_POSTURE.md`
- `docs/discovery/CANONIZATION_WAVE28_SECURITY_POSTURE.md`
- `tools/discovering_canon_audit.php`

## Canonical posture

Security responsibility is split across:

- request-surface policy,
- endpoint/token subscriber,
- mutation-hardening subscriber,
- rate-limit subscriber and services,
- request correlation subscriber,
- response security headers subscriber,
- security preflight tool.

## Non-goals

- No security behavior changes.
- No token value changes.
- No rate-limit default changes.
- No route changes.
- No dependency changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
php tools/security_preflight.php
```

Expected security posture counter:

```text
wave28 security posture findings: 0
```

# Discovering Canonization Wave 30 — Release / Readiness Documentation Taxonomy

Wave 30 documents and machine-checks the release/readiness documentation and local gate surface.

## Scope

Touched files:

- `docs/discovery/RELEASE_READINESS_GATES.md`
- `docs/discovery/CANONIZATION_WAVE30_RELEASE_READINESS_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Canonical posture

Release/readiness posture is documented through:

- RC readiness,
- support matrix,
- known limitations,
- security posture,
- observability posture,
- release readiness gates,
- runtime/security/canonicalization preflight tools,
- Composer quality scripts.

## Non-goals

- No runtime proof execution claim.
- No Composer dependency changes.
- No CI provider binding.
- No release promotion.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
php tools/runtime_preflight.php
php tools/security_preflight.php
```

Expected release/readiness counter:

```text
wave30 release readiness findings: 0
```

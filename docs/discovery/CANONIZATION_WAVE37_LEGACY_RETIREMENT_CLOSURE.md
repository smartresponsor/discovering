# Discovering Canonization Wave 37 — Legacy Retirement Closure

Wave 37 closes residual legacy files that may remain when touched archives are extracted without running earlier retirement PowerShell scripts.

## Scope

Touched overlay files:

- `docs/discovery/CANONIZATION_WAVE37_LEGACY_RETIREMENT_CLOSURE.md`
- `docs/discovery/WAVE37_RETIRED_LEGACY_FILES.txt`
- `tools/discovering_canon_audit.php`

Explicit touched deletions performed by the apply script:

- stale `src/EventSubscriber/Discovery*Subscriber.php` files after the subscriber layer moved to `src/Subscriber/Discovery/`
- stale service-interface files that were moved to `src/ServiceInterface/Discovery/...`

## Safety

The apply script deletes only the paths listed in `docs/discovery/WAVE37_RETIRED_LEGACY_FILES.txt`.

When a file exists, it is copied first to:

```text
.patch-backups/discovering_wave37_legacy_retirement_closure/
```

Missing files are treated as already retired.

## Non-goals

- No repository-wide cleanup.
- No recursive root deletion.
- No namespace rewrites.
- No service wiring changes.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
php tools/local_ci.php
```

Expected legacy retirement counter:

```text
wave37 legacy retirement findings: 0
```

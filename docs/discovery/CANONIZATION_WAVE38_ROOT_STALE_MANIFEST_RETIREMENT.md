# Discovering Canonization Wave 38 — Root Stale Manifest Retirement

Wave 38 retires stale root-level manifest/prompt/patch metadata files after their durable content has been moved into canonical documentation areas.

## Scope

Touched overlay files:

- `docs/discovery/CANONIZATION_WAVE38_ROOT_STALE_MANIFEST_RETIREMENT.md`
- `docs/discovery/WAVE38_RETIRED_ROOT_STALE_FILES.txt`
- `tools/discovering_canon_audit.php`

Explicit touched deletions performed by the apply script:

- `ARCHITECTURE_MANIFEST.md`
- `BOUNDING_MANIFEST.md`
- `PRODUCT_MANIFEST.md`
- `CODEX_CLI_PROMPT.txt`
- `MANIFEST.txt`
- `PATCH_MANIFEST.txt`

## Safety

The apply script deletes only paths listed in `docs/discovery/WAVE38_RETIRED_ROOT_STALE_FILES.txt`.

When a file exists, it is copied first to:

```text
.patch-backups/discovering_wave38_root_stale_manifest_retirement/
```

Missing files are treated as already retired.

## Non-goals

- No repository-wide cleanup.
- No recursive root deletion.
- No docs/manifests deletion.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
php tools/local_ci.php
```

Expected root stale manifest counter:

```text
wave38 root stale manifest findings: 0
```

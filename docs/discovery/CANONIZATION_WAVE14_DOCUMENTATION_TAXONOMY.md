# Discovering Canonization Wave 14 — Documentation Taxonomy

Wave 14 makes the documentation surface explicit without changing production runtime code.

## Scope

Touched files:

- `docs/MANIFEST.md`
- `docs/generated/MANIFEST.md`
- `docs/modules/ROOT/nav.adoc`
- `docs/modules/ROOT/pages/canonization.adoc`
- `docs/modules/ROOT/pages/manifests.adoc`
- `docs/modules/ROOT/pages/generated.adoc`
- `docs/discovery/CANONIZATION_WAVE14_DOCUMENTATION_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Canonical posture

Discovering documentation is now categorized as:

- Antora entry surface: `docs/modules/ROOT/`
- Detailed narrative and operational Markdown: `docs/discovery/`
- Durable producer manifests: `docs/manifests/`
- Generated artifacts: `docs/generated/`

## Non-goals

- No production PHP behavior changed.
- No Symfony config changed.
- No generated artifacts were produced.
- No root cleanup beyond previous touched-file waves.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected documentation taxonomy counter:

```text
wave14 documentation taxonomy findings: 0
```

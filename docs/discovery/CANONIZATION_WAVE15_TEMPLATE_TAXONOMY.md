# Discovering Canonization Wave 15 — Template / UI Taxonomy

Wave 15 makes the Twig template surface explicit without redesigning UI or changing production behavior.

## Scope

Touched files:

- `templates/MANIFEST.md`
- `templates/discovery/MANIFEST.md`
- `templates/management/MANIFEST.md`
- `templates/management/discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE15_TEMPLATE_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Canonical posture

Templates are split by interaction surface:

- Public surface: `templates/discovery/`
- Operator/management surface: `templates/management/`
- Discovery-specific operator surface: `templates/management/discovery/`

## Non-goals

- No Twig redesign.
- No breadcrumb/layout experimentation.
- No route/controller changes.
- No JavaScript platform work.
- No public/operator behavior change.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected template taxonomy counter:

```text
wave15 template taxonomy findings: 0
```

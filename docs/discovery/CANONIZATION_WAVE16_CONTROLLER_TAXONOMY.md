# Discovering Canonization Wave 16 — Controller Taxonomy

Wave 16 makes the HTTP controller surface explicit without moving controllers or changing behavior.

## Scope

Touched files:

- `src/Controller/MANIFEST.md`
- `src/Controller/Discovery/MANIFEST.md`
- `src/Controller/Management/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE16_CONTROLLER_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Canonical posture

Discovering now treats controllers as two HTTP interaction surfaces:

- Public discovery surface: `src/Controller/Discovery/`
- Operator management surface: `src/Controller/Management/`

## Non-goals

- No route path changes.
- No route name changes.
- No controller namespace moves.
- No template rewiring.
- No business logic changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected controller taxonomy counter:

```text
wave16 controller taxonomy findings: 0
```

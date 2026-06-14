# Discovering Canonization Wave 45 — Evidence Summary Markdown

Wave 45 adds a human-readable Markdown summary generator for generated evidence JSON.

## Scope

Touched files:

- `tools/evidence_summary.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/EVIDENCE_INDEX.md`
- `docs/discovery/CANONIZATION_WAVE45_EVIDENCE_SUMMARY_MARKDOWN.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New command:

```bash
php tools/evidence_summary.php
```

Default input:

```text
var/discovery/evidence/evidence_index.json
```

Default output:

```text
var/discovery/evidence/evidence_summary.md
```

Composer alias:

```bash
composer verify:evidence-summary
```

## Non-goals

- No runtime proof claim.
- No dependency changes.
- No vendor generation.
- No destructive cleanup.

## Verification

Run:

```bash
php -l tools/evidence_summary.php
php tools/discovering_canon_audit.php
php tools/structural_closure_evidence.php
php tools/evidence_index.php
php tools/evidence_summary.php
```

Expected evidence summary counter:

```text
wave45 evidence summary findings: 0
```

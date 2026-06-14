# Discovering Canonization Wave 46 — Evidence Bundle Export

Wave 46 adds a ZIP exporter for generated Discovering evidence artifacts.

## Scope

Touched files:

- `tools/evidence_bundle.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/EVIDENCE_INDEX.md`
- `docs/discovery/CANONIZATION_WAVE46_EVIDENCE_BUNDLE_EXPORT.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New command:

```bash
php tools/evidence_bundle.php
```

Default output:

```text
var/discovery/evidence/discovering_evidence_bundle.zip
```

Composer alias:

```bash
composer verify:evidence-bundle
```

The bundle includes available files from:

- `structural_closure_evidence.json`
- `runtime_dependency_evidence.json`
- `console_container_evidence.json`
- `evidence_index.json`
- `evidence_summary.md`
- generated `bundle_manifest.json`

Missing strict evidence files are listed in the manifest and do not block bundle export.

## Non-goals

- No runtime proof claim.
- No dependency changes.
- No vendor generation.
- No destructive cleanup.

## Verification

Run:

```bash
php -l tools/evidence_bundle.php
php tools/discovering_canon_audit.php
php tools/structural_closure_evidence.php
php tools/evidence_index.php
php tools/evidence_summary.php
php tools/evidence_bundle.php
```

Expected evidence bundle counter:

```text
wave46 evidence bundle findings: 0
```

# Discovering Canonization Wave 48 — Evidence Artifact Retention Policy

Wave 48 documents generated evidence artifact retention and adds a safe, scoped cleanup helper.

## Scope

Touched files:

- `docs/discovery/EVIDENCE_ARTIFACT_RETENTION.md`
- `tools/evidence_artifact_cleanup.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/EVIDENCE_INDEX.md`
- `docs/discovery/EVIDENCE_HANDOFF.md`
- `docs/discovery/CANONIZATION_WAVE48_EVIDENCE_ARTIFACT_RETENTION.md`
- `tools/discovering_canon_audit.php`

## Functional change

New cleanup helper:

```bash
php tools/evidence_artifact_cleanup.php --dry-run
php tools/evidence_artifact_cleanup.php
```

Composer aliases:

```bash
composer evidence:cleanup:dry-run
composer evidence:cleanup
```

The cleanup helper removes only known generated files under `var/discovery/evidence/`.

## Non-goals

- No repository-wide cleanup.
- No recursive project-root deletion.
- No source file deletion.
- No runtime proof claim.

## Verification

Run:

```bash
php -l tools/evidence_artifact_cleanup.php
php tools/discovering_canon_audit.php
php tools/evidence_artifact_cleanup.php --dry-run
```

Expected evidence retention counter:

```text
wave48 evidence artifact retention findings: 0
```

# Discovering Evidence Artifact Retention Policy

Generated evidence artifacts are local proof outputs. They are useful for review and release handoff, but they should not be confused with source code.

## Generated evidence directory

Default directory:

```text
var/discovery/evidence/
```

Generated files:

- `structural_closure_evidence.json`
- `runtime_dependency_evidence.json`
- `console_container_evidence.json`
- `evidence_index.json`
- `evidence_summary.md`
- `discovering_evidence_bundle.zip`

## Source-controlled files

Source-controlled evidence tooling and docs live under:

- `tools/*evidence*.php`
- `docs/discovery/EVIDENCE_INDEX.md`
- `docs/discovery/EVIDENCE_HANDOFF.md`
- `docs/discovery/PROVISIONED_RUNTIME_CHECKLIST.md`
- `docs/discovery/RUNTIME_PROOF_BOUNDARY.md`

## Retention rule

Generated evidence artifacts may be attached to release/PR/review handoff, but should be regenerated rather than manually edited.

## Cleanup rule

Do not delete the repository or recursively clear project roots.

Safe cleanup is limited to generated evidence files under:

```text
var/discovery/evidence/
```

Preferred cleanup command:

```bash
php tools/evidence_artifact_cleanup.php --dry-run
php tools/evidence_artifact_cleanup.php
```

## Interpretation

A missing runtime dependency artifact means strict runtime proof has not been generated on this machine.

A missing console/container artifact means Symfony console/container proof has not been generated on this machine.

Structural closure remains independently provable through `structural_closure_evidence.json`.

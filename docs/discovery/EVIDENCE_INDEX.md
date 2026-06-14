# Discovering Evidence Index

Discovering uses multiple evidence boundaries:

- structural closure,
- runtime dependency proof,
- console/container proof.

Each boundary writes a JSON artifact under:

```text
var/discovery/evidence/
```

## Evidence files

- `structural_closure_evidence.json`
- `runtime_dependency_evidence.json`
- `console_container_evidence.json`
- `evidence_index.json`

## Index command

```bash
php tools/evidence_index.php
```

Composer alias:

```bash
composer verify:evidence-index
```

The index can fail when strict runtime evidence files are missing or failing. That is expected on an under-provisioned machine. Structural closure remains independently provable through `structural_closure_evidence.json`.


## Markdown summary

Generate a human-readable summary from the evidence index:

```bash
php tools/evidence_summary.php
```

Default output:

```text
var/discovery/evidence/evidence_summary.md
```


## Evidence bundle

Export generated evidence artifacts into a handoff ZIP:

```bash
php tools/evidence_bundle.php
```

Default output:

```text
var/discovery/evidence/discovering_evidence_bundle.zip
```


## Handoff guide

Use the handoff guide when sharing evidence artifacts:

```text
docs/discovery/EVIDENCE_HANDOFF.md
```


## Retention policy

Generated evidence artifact retention is documented in:

```text
docs/discovery/EVIDENCE_ARTIFACT_RETENTION.md
```

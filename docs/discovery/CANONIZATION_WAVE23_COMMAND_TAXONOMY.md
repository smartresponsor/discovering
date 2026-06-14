# Discovering Canonization Wave 23 — Command Taxonomy Hardening

Wave 23 documents and machine-checks the Discovering CLI command surface after the Wave 11 command-name cleanup.

## Scope

Touched files:

- `src/Command/MANIFEST.md`
- `src/Command/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE23_COMMAND_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

`src/Command/Discovery` currently contains 15 Symfony console commands.

All current primary command names use the `app:discovery:*` family.

Three old `discovering:*` names are retained only as compatibility aliases:

- `discovering:rebuild`
- `discovering:rollback:plan`
- `discovering:rollback:execute`

## Canonical posture

Commands are CLI adapters, not business services. They should stay under `src/Command/Discovery` and delegate execution to service-layer classes.

## Non-goals

- No command renames.
- No alias removal.
- No service rewiring.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected command taxonomy counter:

```text
wave23 command taxonomy findings: 0
```

# Discovering Canonization Wave 47 — Evidence Handoff README

Wave 47 adds a human-readable evidence handoff document.

## Scope

Touched files:

- `docs/discovery/EVIDENCE_HANDOFF.md`
- `docs/discovery/CANONIZATION_WAVE47_EVIDENCE_HANDOFF_README.md`
- `docs/discovery/EVIDENCE_INDEX.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional posture

This wave is documentation and audit only.

It explains:

- how to generate structural closure evidence,
- how to generate provisioned runtime evidence,
- which artifact to hand off,
- how to interpret structural/runtime/container PASS and FAIL independently.

## Non-goals

- No runtime proof claim.
- No dependency changes.
- No vendor generation.
- No destructive cleanup.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected evidence handoff counter:

```text
wave47 evidence handoff findings: 0
```

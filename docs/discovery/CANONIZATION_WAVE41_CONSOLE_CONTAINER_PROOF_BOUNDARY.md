# Discovering Canonization Wave 41 — Console / Container Proof Boundary

Wave 41 adds a dedicated Symfony console/container evidence boundary.

## Scope

Touched files:

- `tools/console_container_evidence.php`
- `tools/MANIFEST.md`
- `composer.json`
- `docs/discovery/RUNTIME_PROOF_BOUNDARY.md`
- `docs/discovery/CANONIZATION_WAVE41_CONSOLE_CONTAINER_PROOF_BOUNDARY.md`
- `docs/discovery/RELEASE_READINESS_GATES.md`
- `tools/discovering_canon_audit.php`

## Functional change

New command:

```bash
php tools/console_container_evidence.php
```

Default output:

```text
var/discovery/evidence/console_container_evidence.json
```

Composer alias:

```bash
composer verify:console-container-evidence
```

The evidence tool checks:

- vendor runtime preflight,
- `bin/console list --raw`,
- `bin/console cache:clear --env=dev --no-warmup`,
- `bin/console lint:container --env=dev`.

## Non-goals

- No dependency installation.
- No vendor generation.
- No service/container rewiring.
- No claim that console/container proof passes on an unprovisioned machine.

## Verification

Run:

```bash
php -l tools/console_container_evidence.php
php tools/discovering_canon_audit.php
php tools/structural_closure_evidence.php
```

Expected console/container boundary counter:

```text
wave41 console container boundary findings: 0
```

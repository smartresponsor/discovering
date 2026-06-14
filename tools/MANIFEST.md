# Discovering local tool taxonomy

Repository-local tools are lightweight verification entrypoints. They should be deterministic and safe to run from the repository root.

## Current helpers

- `discovering_canon_audit.php` — structural canonicalization audit.
- `runtime_preflight.php` — runtime/environment baseline checks.
- `qa_guard.php` — safe wrappers for optional QA and OpenAPI commands.
- `docblock_policy_check.php` — guards descriptive documentation-grade docblocks.
- `security_preflight.php` — checks security subscriber, OpenAPI and readiness documentation wiring.
- `lint_php.php` — PHP syntax lint for `src/` and `tests/`.
- `local_ci.php` — Composer-free local structural CI wrapper.
- `structural_closure_evidence.php` — writes structural closure evidence JSON under `var/discovery/evidence/`.
- `runtime_dependency_evidence.php` — writes strict runtime/vendor/test dependency evidence JSON under `var/discovery/evidence/`.
- `console_container_evidence.php` — writes Symfony console/container proof evidence JSON under `var/discovery/evidence/`.
- `evidence_index.php` — writes a summary index for generated evidence JSON files.
- `evidence_summary.php` — writes a Markdown summary from the generated evidence index.
- `evidence_bundle.php` — exports generated evidence artifacts into a ZIP bundle.
- `evidence_artifact_cleanup.php` — removes only known generated evidence artifacts from `var/discovery/evidence/`.

## Rules

- Tools must not delete or rewrite the repository.
- Composer-free wrappers should call only repository-local PHP tools.
- Tools should fail with clear exit codes.
- Tools should not require Composer vendor unless their command explicitly says so.
- Security/runtime preflight tools must follow canonical paths introduced by taxonomy waves.

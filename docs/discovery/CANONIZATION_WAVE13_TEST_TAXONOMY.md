# Discovering Canonization Wave 13 — Test Taxonomy

Wave 13 documents the test tree as an explicit verification surface.

## Intent

Discovering already has broad test coverage across unit, functional, contract and behavioral layers. The cleanup target is not to rewrite tests, but to make the intended taxonomy machine-checkable so future cleanup waves do not collapse everything into one broad bucket.

## Canonical test buckets

- `tests/Unit/Discovery/` for focused class-level checks.
- `tests/Functional/Discovery/` for Symfony kernel, HTTP, form and container checks.
- `tests/Contract/Discovery/` for stable external API/management contracts.
- `tests/Behavioral/Discovery/` for scenario-level service behavior.
- `tests/Support/` for test-only helpers and factories.

## Guardrails added

- Every canonical test bucket now has a `MANIFEST.md`.
- The root `tests/MANIFEST.md` documents allowed root-level PHP files.
- `tools/discovering_canon_audit.php` reports `wave13_test_taxonomy_findings`.

## Non-goals

- No production code was changed.
- No test class was moved in this wave.
- No PHPUnit configuration was changed.
- No runtime dependency was introduced.

## Follow-up candidates

Later waves can review individual tests for bucket drift, but only when a move is obvious and all namespaces/references are updated in the same touched-file patch.

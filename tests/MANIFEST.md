# Discovering Test Taxonomy

The Discovering test tree is split by verification scope rather than by legacy implementation folders.

## Canonical buckets

- `tests/Unit/Discovery/` — focused DTO, value object, service, repository/backend and small policy tests.
- `tests/Functional/Discovery/` — Symfony kernel, HTTP, controller, form and container-level tests.
- `tests/Contract/Discovery/` — externally visible API/envelope/management contract assertions.
- `tests/Behavioral/Discovery/` — cross-service behavior and scenario-level learning/mode tests.
- `tests/Support/` — reusable test fixtures, factories and base helpers only.

## Rules

- New Discovering tests must live in one of the canonical buckets above.
- Test classes must keep the `Test` suffix unless the file is an abstract base, trait, assertion helper, factory or support helper.
- `tests/bootstrap.php` is the only allowed root-level PHP file in `tests/`.
- Support helpers must not become hidden production services; production code belongs under `src/`.

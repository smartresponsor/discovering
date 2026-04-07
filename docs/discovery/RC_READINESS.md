# RC Readiness

## Current status

Discovering is currently positioned as an **Ecosystem RC candidate**.

The repository already provides:

- Symfony-oriented runtime and management surfaces
- structured discovery and rebuild contracts
- Antora-compatible producer documentation
- OpenAPI/Nelmio integration-ready wiring
- security baseline event subscribers
- docblock-safe QA policy and preflight checks

## Remaining RC gates

The following gates still require confirmation in a fully provisioned environment:

- real `phpstan` execution
- real `php-cs-fixer` execution in docblock-safe mode
- full PHPUnit execution with required XML/DOM extensions
- live Nelmio dump and Swagger UI routes
- final auth/authz verification for management and mutation routes

## RC interpretation

The repository should be treated as a strong RC candidate, not yet a final release candidate, until the above gates are proven by CI or an equivalent controlled runtime.

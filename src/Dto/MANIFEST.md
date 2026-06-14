# Discovering DTO taxonomy

DTOs are immutable transfer/payload objects. They are not Doctrine entities, Symfony forms, controllers or services.

## Canonical bucket

- `src/Dto/Discovery/` contains Discovering DTOs and typed request/result/surface payloads.

## Rules

- DTO classes should be `readonly`.
- DTO namespaces must stay under `App\Dto\Discovery`.
- DTO files must not declare Doctrine entities, Symfony controllers, Symfony forms or services.
- DTOs may model requests, results, management surfaces, diagnostics, event payloads and topology snapshots.

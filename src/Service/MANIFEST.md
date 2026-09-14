# Discovering service taxonomy

Discovering service code is organized by capability and technical role.

## Canonical bucket

- `src/Service/Discovery/` contains Discovering business/application services.

## Rules

- Service classes should keep type-identifiable suffixes such as `Service`, `Builder`, `Factory`, `Provider`, `Store`, `Logger`, `Limiter`, `Backend`, `Resolver`, `Executor`, `Indexer`, `Repository`, `Registry`, `Namer`, `Transport`, `Policy`, `Contract`, `Serializer`, `Encoder`, or `Decoder`.
- Service interfaces belong under `src/ServiceInterface/`, not inside `src/Service/`.
- Persistence entities belong under `src/Entity/`.
- Symfony controllers, commands, subscribers, forms and DTOs belong in their own type layers.

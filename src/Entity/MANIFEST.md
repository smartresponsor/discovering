# Discovering entity taxonomy

Discovering uses Doctrine entities for persisted database state.

## Canonical buckets

- `src/Entity/Discovery/` contains Discovery-owned Doctrine entities.

## Rules

- Doctrine entity class names should end with `Entity`.
- Discovery-owned database tables must use the `discovery_` prefix.
- Doctrine entities remain mutable persistence models and must not be converted to readonly DTO/value-object style.
- Entity-first posture requires schema/migration visibility for each entity-owned table.

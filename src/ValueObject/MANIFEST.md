# Discovering value object taxonomy

Value objects model small immutable semantic values. They are not Doctrine entities, DTO bags, Symfony forms, controllers or services.

## Canonical bucket

- `src/ValueObject/Discovery/` contains Discovering value objects.

## Rules

- Value object classes should be `readonly`.
- Value object namespaces must stay under `App\ValueObject\Discovery`.
- Value objects should express domain semantics and invariants, not transport payload shape.
- Value objects must not declare Doctrine entities, Symfony controllers, Symfony forms or services.

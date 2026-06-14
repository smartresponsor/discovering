# Discovering form taxonomy

Symfony forms are kept in the dedicated `src/Form` type layer.

## Canonical bucket

- `src/Form/Discovery/` contains Discovering Symfony form types.

## Rules

- Form type classes must end with `Type`.
- Form type files must end with `Type.php`.
- Form namespaces must stay under `App\Form\Discovery`.
- Form types should extend Symfony `AbstractType`.
- Query/search forms that feed readonly DTOs should avoid mutating those DTOs directly.

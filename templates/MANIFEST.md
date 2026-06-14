# Discovering template taxonomy

Discovering templates are organized by interaction surface instead of by incidental controller location.

## Canonical buckets

- `templates/base.html.twig` is the shared Symfony/Twig document shell.
- `templates/discovery/` contains public discovery/search surfaces.
- `templates/management/` contains operator and management surfaces.
- `templates/management/discovery/` contains discovery-specific management pages.

## Rules

- Concrete page templates should keep the `.html.twig` suffix.
- Public discovery templates should stay under `templates/discovery/`.
- Operator/management templates should stay under `templates/management/`.
- Shared management discovery primitives may live under `templates/management/discovery/` when they are discovery-specific.
- Do not introduce a custom frontend application inside this component unless a separate UI platform wave explicitly owns it.

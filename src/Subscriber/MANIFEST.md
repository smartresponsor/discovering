# Discovering subscriber taxonomy

Symfony event subscribers are kept in the dedicated `src/Subscriber` type layer.

## Canonical bucket

- `src/Subscriber/Discovery/` contains Discovering HTTP/kernel event subscribers.

## Rules

- Subscriber classes must keep the `Subscriber` suffix.
- Subscriber namespaces must stay under `App\Subscriber`.
- Legacy `src/EventSubscriber` is not a canonical layer for this component.
- Symfony event subscribers should not be mixed with operation/operator event DTOs or event-log persistence models.

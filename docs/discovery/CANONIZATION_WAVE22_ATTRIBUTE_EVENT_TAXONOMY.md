# Discovering Canonization Wave 22 — Attribute / Event / Subscriber Taxonomy

Wave 22 documents the current event-related posture and prevents mixing Symfony subscribers with operation/operator event payloads.

## Scope

Touched files:

- `src/Subscriber/MANIFEST.md`
- `src/Subscriber/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE22_ATTRIBUTE_EVENT_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

- `src/Subscriber/Discovery` exists and contains five Symfony event subscribers.
- `src/Event` is currently absent.
- `src/Attribute` is currently absent.
- `src/EventSubscriber` has been retired by previous waves.
- Event-like DTO/entity names currently represent operation/operator event logs, not Symfony domain events.

## Canonical posture

- Symfony event subscribers belong under `src/Subscriber/Discovery`.
- Operation/operator event DTOs belong under `src/Dto/Discovery`.
- Operation/operator event persistence models belong under `src/Entity/Discovery`.
- Event-log stores and serializers belong under `src/Service/Discovery/...` and `src/ServiceInterface/Discovery/...`.
- A future real Symfony/domain event layer should use `src/Event/Discovery`, but it should be introduced only when actual event classes exist.

## Non-goals

- No subscriber rewrites.
- No domain event introduction.
- No attribute introduction.
- No listener layer introduction.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected taxonomy counter:

```text
wave22 attribute event taxonomy findings: 0
```

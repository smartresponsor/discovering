# Discovering DTOs

This directory contains Discovering immutable data transfer objects.

## Current DTO families

- Query/request DTOs: `DiscoveryQuery`, `ReindexRequest`, `LibsourceEventLogQuery`.
- Result DTOs: `DiscoveryResult`, `DiscoveryHit`, rebuild/rollback results.
- Management surface DTOs: briefing, playbook, libsource and directory-backed family surface records.
- Diagnostics/topology DTOs: backend reachability, platform diagnostics, state topology and coverage entries.
- Operation/event DTOs: operation events and operator-event records.

## Rules

- DTO classes must use `readonly class` or `final readonly class`.
- DTO class names should be descriptive payload names; the `Dto` suffix is not required in this ecosystem.
- DTOs must not extend Symfony or Doctrine infrastructure classes.
- DTOs should not receive services through constructors.

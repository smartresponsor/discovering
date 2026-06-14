# Discovering value objects

This directory contains Discovering immutable value objects.

## Current value objects

- `DiscoveryDocument` — semantic document value used by discovery indexing/search flows.
- `DiscoveryScope` — semantic discovery scope value.

## Rules

- Value object classes must use `readonly class` or `final readonly class`.
- Value object names should represent domain concepts, not transport messages.
- Value objects must not extend Symfony or Doctrine infrastructure classes.
- DTOs belong under `src/Dto/Discovery`; persistence models belong under `src/Entity/Discovery`.

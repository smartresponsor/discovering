# Discovery form types

This directory contains Discovering Symfony form types.

## Current form types

- `DiscoverySearchType` — public discovery query/search form.

## Boundary rule

`DiscoverySearchType` is a query/input surface. It must not attempt to mutate the readonly `DiscoveryQuery` DTO during submit.

The controller remains the authoritative boundary for building `DiscoveryQuery` from the HTTP request.

## Rules

- Query/search fields should use `mapped => false`.
- Form types should remain presentation/input boundary types, not business services.
- Form type names must keep the `Type` suffix.

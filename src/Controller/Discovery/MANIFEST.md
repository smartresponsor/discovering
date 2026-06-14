# Public discovery controllers

This directory contains public discovery HTTP controllers.

## Current controller

- `DiscoveryController` owns the public discovery search, feedback, learning and JSON endpoints.

## Rules

- Route names should use the `app_discovery_*` prefix.
- Route paths should stay under `/discovery`.
- Public controllers should render templates under `templates/discovery/`.
- Public controllers should not render management templates or expose management-only actions.

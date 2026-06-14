# Discovery management controllers

This directory contains operator-facing management controllers.

## Current controllers

- `DiscoveryOverviewManagementController`
- `DiscoveryManagementController`
- `DiscoveryLibsourceManagementController`
- `DiscoveryLibsourceLogManagementController`
- `DiscoveryPlaybookManagementController`
- `DiscoveryBriefingManagementController`
- `AbstractDirectoryBackedFamilyManagementController`

## Rules

- Concrete management controller class names should end with `ManagementController`.
- Management route names should use the `app_management_discovery_*` prefix.
- Management route paths should stay under `/management/discovery`.
- Management controllers should render templates under `templates/management/discovery/`.
- Shared abstract controller primitives may omit the `ManagementController` suffix only when they are abstract support classes.

# Management template surface

This directory contains operator-facing templates.

## Canonical posture

Management templates are separated from public templates so diagnostic, rebuild, export and source-management flows do not leak into the public discovery surface.

## Rules

- Management pages should live under a capability-specific directory, for example `templates/management/discovery/`.
- Shared management templates may be introduced only when at least two management capabilities need them.
- Discovery-specific management primitives remain under `templates/management/discovery/`.

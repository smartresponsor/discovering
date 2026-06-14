# Discovery management template surface

This directory contains operator-facing discovery management templates.

## Current pages

- `overview.html.twig` renders the management overview.
- `libsource.html.twig` renders libsource diagnostics and actions.
- `libsource_log.html.twig` renders the libsource event log.
- `playbook.html.twig` renders playbook source management.
- `briefing.html.twig` renders briefing source management.
- `directory_backed_family_management.html.twig` is the shared discovery-specific management primitive for directory-backed families.

## Rules

- Discovery management templates may expose operator actions and diagnostics.
- Public discovery templates must not extend management templates.
- Discovery-specific reusable primitives can stay here; generic management layout extraction should be a separate wave.

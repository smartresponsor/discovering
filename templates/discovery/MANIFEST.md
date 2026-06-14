# Public discovery template surface

This directory contains user-facing discovery/search Twig templates.

## Current pages

- `index.html.twig` renders the public discovery workspace.

## Rules

- Public discovery templates should not contain operator-only management actions.
- Public templates may render query/search result DTOs, but they should not perform business decisions.
- Route/controller ownership remains in Symfony controllers; Twig remains a rendering surface.

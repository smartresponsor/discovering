# Discovering service configuration

This directory contains component-specific Symfony service wiring.

## Current files

- `discovery.yaml` — Discovering parameters, service tags and service-interface aliases.

## Rules

- ServiceInterface aliases for Discovering contracts belong here.
- Component-specific parameters should use `app.discovery.*` naming.
- Root `config/services.yaml` should import this file and remain a general Symfony aggregator.

# Discovering package configuration

This directory contains Symfony bundle/package configuration.

## Current package configs

- `framework.yaml`
- `twig.yaml`
- `doctrine.yaml`
- `nelmio_api_doc.php`
- `test/`

## Rules

- Package configuration belongs here.
- Component service aliases and tags belong under `config/services/discovery.yaml`.
- Runtime bundle activation must match `composer.json` dependencies.
- Doctrine migrations runtime configuration should not be enabled until the migrations package is present.

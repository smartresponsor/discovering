# Discovering CI and runtime baseline

This repository now defines a minimal reproducible CI/runtime baseline for local development and remote verification.

## Local install

```bash
composer install
```

## Local verification commands

```bash
composer validate:composer
composer lint:php
composer test:unit
composer test:contract
composer test:behavioral
composer test:functional
composer ci
```

## Expected PHP/runtime baseline

- PHP 8.4
- `ext-json`
- `ext-pdo`
- `ext-pdo_sqlite`

## Test suites

- `Unit` — isolated component/unit checks
- `Contract` — HTTP envelope and schema expectations
- `Behavioral` — discovery mode and feedback-learning scenarios
- `Functional` — HTTP/UI and management/runtime flows

## GitHub Actions baseline

The workflow at `.github/workflows/ci.yaml` performs:

1. Composer metadata validation
2. Dependency installation
3. PHP linting
4. Unit tests
5. Contract tests
6. Behavioral tests
7. Functional tests

## Operational intent

This baseline does not claim production readiness by itself.
It provides a reproducible gate so that bootstrap, contracts, behavior, and HTTP flows can be re-verified on every branch update.

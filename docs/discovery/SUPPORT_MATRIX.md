# Support Matrix

## Runtime baseline

- PHP: `>= 8.4`
- Required extensions for application runtime:
  - `json`
  - `pdo`
  - `pdo_sqlite`
- Required extensions for test and QA runtime:
  - `dom`
  - `mbstring`
  - `xml`
  - `xmlwriter`

## Repository-local tooling

The repository expects the following developer tooling for full RC proving:

- Composer v2
- PHPUnit 11.x
- PHPStan 2.x
- PHP CS Fixer 3.x
- NelmioApiDocBundle 5.x

## Supported repository modes

- ecosystem brick inside the Smart Responsor component landscape
- local standalone Symfony-oriented application mode
- local SQLite-first indexing mode
- integration-ready Meilisearch mode when configured

## Not yet treated as first-class deployment targets

- distributed multi-node production clusters
- Kubernetes-native autoscaling posture
- service-mesh-based deployments
- vector-search-only retrieval mode

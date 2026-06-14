# Discovering service interface taxonomy

Service interfaces are kept in a dedicated mirror layer instead of being colocated inside `src/Service`.

## Canonical bucket

- `src/ServiceInterface/Discovery/` contains Discovering service contracts.

## Rules

- Service interface files must end with `Interface.php`.
- Service interface namespaces must stay under `App\ServiceInterface`.
- Runtime implementations stay under `src/Service`.
- Only externally meaningful contracts should be mirrored here; not every internal service class needs an interface.

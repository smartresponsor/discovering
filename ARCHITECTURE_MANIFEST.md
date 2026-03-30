# Architecture Manifest

## Canon

- single Symfony-oriented application
- only `App\` namespace
- only `src/` as production code root
- no `/src/Domain/`
- no alternative root namespace
- keep path, layer, responsibility and naming aligned

## Structural direction

Use mirrored `Service` and `ServiceInterface` trees. Keep controllers thin. Let services orchestrate behavior. Let DTOs, forms and value objects shape input and response flow.

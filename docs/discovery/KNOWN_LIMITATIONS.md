# Known Limitations

## Current limitations

- Full QA proving depends on Composer and the complete PHP extension set.
- OpenAPI wiring is integration-ready, but generated Swagger/OpenAPI artifacts still depend on Nelmio being installed in the active environment.
- Management and mutation routes use token-based protection; broader role models are still intentionally light.
- Discovery remains stronger in operator and rebuild posture than in advanced retrieval semantics such as faceting and richer authority models.

## Intentionally deferred

The following are intentionally not treated as RC blockers for this repository stage:

- GraphQL
- gRPC
- service mesh
- sidecars
- vector database integration
- distributed consensus patterns
- autoscaling and traffic cohort machinery

## Why these are deferred

Discovering is currently optimized as a Symfony-oriented ecosystem component first. The repository should remain coherent with neighboring components instead of prematurely adopting infrastructure-heavy patterns that are not yet materially required.

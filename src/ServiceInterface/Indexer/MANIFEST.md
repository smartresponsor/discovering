# Discovery Indexer Interface Manifest

Indexer contracts define rebuild and mutation behavior for discovery documents.

Rules:

- Indexer contracts orchestrate `DiscoveryDocument` and rebuild DTOs, not HTTP or template concerns.
- Interfaces must end with `Interface`.
- Implementations belong under `src/Service/Discovery/Indexer`.

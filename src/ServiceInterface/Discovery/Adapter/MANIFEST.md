# Discovery Adapter Interface Manifest

Adapter interfaces define the stable indexing/search backend capability surface.

Rules:

- Adapter contracts stay storage/search-engine agnostic.
- Interfaces must end with `Interface`.
- Implementations belong under `src/Service/Discovery/Adapter`.

# Discovery Backend Interface Manifest

Backend interfaces define the stable indexing/search backend capability surface.

Rules:

- Backend contracts stay storage/search-engine agnostic.
- Interfaces must end with `Interface`.
- Implementations belong under `src/Service/Discovery/Backend`.

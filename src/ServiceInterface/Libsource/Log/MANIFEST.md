# Discovery Libsource Log Interface Manifest

Libsource log contracts define append/read/clear behavior for operator event history.

Rules:

- Store contracts must end with `StoreInterface`.
- DTO payloads must stay immutable and belong to `src/Dto/Discovery`.
- Persistence implementations may be configurable, file-backed, ephemeral, or Doctrine-backed under `src/Service/Discovery/Libsource/Log`.

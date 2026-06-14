# Discovery Rebuild Interface Manifest

Rebuild contracts define staging capability and evidence persistence boundaries for index rebuild workflows.

Rules:

- Rebuild contracts remain separate from command/controller orchestration.
- Interfaces must end with `Interface`.
- Implementations belong under `src/Service/Discovery/Rebuild` or existing adapter/evidence service folders.

# Discovery Overview Interface Manifest

Overview contracts define read-model surfaces for management and diagnostic summaries.

Rules:

- Overview services return DTO read models.
- Interfaces must end with `Interface`.
- Controllers depend on this contract layer rather than concrete overview builders.

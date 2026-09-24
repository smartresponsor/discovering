# Discovery Rate Limit Interface Manifest

Rate-limit contracts define storage-independent counting windows for discovery endpoint protection.

Rules:

- Store contracts must not expose HTTP or controller concerns.
- Interfaces must end with `Interface`.
- Implementations belong under `src/Service/Discovery/RateLimit`.

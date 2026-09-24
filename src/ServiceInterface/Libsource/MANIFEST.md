# Discovery Libsource Interface Manifest

Libsource contracts define integration-facing boundaries for operator-visible source metadata and event-log behavior.

Rules:

- Shared libsource contracts stay under `App\ServiceInterface\Discovery\Libsource`.
- Log-store contracts stay under `App\ServiceInterface\Discovery\Libsource\Log`.
- Implementations remain in `src/Service/Discovery/Libsource`.

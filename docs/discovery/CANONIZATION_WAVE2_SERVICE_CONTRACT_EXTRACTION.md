# Discovering Canonization Wave 2 — Service Contract Extraction

## Scope

Wave 2 moves service contracts out of `src/Service/...` and into the existing mirrored `src/ServiceInterface/...` layer.

This is a targeted structural cleanup wave. It does not rename the root `App\` namespace, does not move Doctrine entities, does not change database table names, and does not rewrite the whole repository.

## Contract moves

| Retired legacy path | Canonical path |
| --- | --- |
| `src/Service/Discovery/Diagnostics/DiscoveryProbeTransportInterface.php` | `src/ServiceInterface/Discovery/Diagnostics/DiscoveryProbeTransportInterface.php` |
| `src/Service/Discovery/DiscoveryFeedbackStoreInterface.php` | `src/ServiceInterface/Discovery/DiscoveryFeedbackStoreInterface.php` |
| `src/Service/Discovery/Libsource/Log/LibsourceOperatorEventLogStoreInterface.php` | `src/ServiceInterface/Discovery/Libsource/Log/LibsourceOperatorEventLogStoreInterface.php` |
| `src/Service/Discovery/Operations/DiscoveryOperationEventLogStoreInterface.php` | `src/ServiceInterface/Discovery/Operations/DiscoveryOperationEventLogStoreInterface.php` |
| `src/Service/Discovery/RateLimit/DiscoveryRateLimitStoreInterface.php` | `src/ServiceInterface/Discovery/RateLimit/DiscoveryRateLimitStoreInterface.php` |
| `src/Service/Discovery/Rebuild/DiscoveryRebuildEvidenceStoreInterface.php` | `src/ServiceInterface/Discovery/Rebuild/DiscoveryRebuildEvidenceStoreInterface.php` |
| `src/Service/Discovery/Source/Repository/DiscoverySourceRecordRepositoryInterface.php` | `src/ServiceInterface/Discovery/Source/Repository/DiscoverySourceRecordRepositoryInterface.php` |
| `src/Service/Discovery/Support/DirectoryBackedFamilyManagementActionServiceInterface.php` | `src/ServiceInterface/Discovery/Support/DirectoryBackedFamilyManagementActionServiceInterface.php` |

## Runtime wiring

`config/services.yaml` now aliases the moved interfaces from `App\ServiceInterface\Discovery\...` to the existing concrete services.

The wave also updates references in controllers, commands, services, and unit tests so type hints point to the interface layer instead of the implementation layer.

## Guardrail

The PowerShell apply script only retires the eight legacy interface files listed above, and only by moving them to `.patch-backups/discovering_wave2_service_contracts/...` before extracting the touched archive.

No repository-wide cleanup, overwrite, or deletion is performed.

## Next recommended wave

Wave 3 should address subscriber/controller/service naming and folder shape, especially `src/EventSubscriber` versus the canonical subscriber layer. Keep Doctrine entity normalization separate from this wave.

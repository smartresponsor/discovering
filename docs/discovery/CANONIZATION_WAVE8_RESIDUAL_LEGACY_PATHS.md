# Discovering Canonization Wave 8: Residual Legacy Path Retirement

Wave 8 is a narrow cleanup wave after the service contract extraction and subscriber layer alignment waves.
It does not introduce new runtime classes and does not rewrite the repository tree.

## Scope

This wave retires only residual legacy files that may still physically exist in a working tree after overlay-style patch application.
The canonical replacements were already introduced in earlier waves:

- Service contracts belong under `src/ServiceInterface/Discovery/...`.
- Symfony event subscribers belong under `src/Subscriber/Discovery/...`.

## Retired legacy service interface paths

- `src/Service/Discovery/Diagnostics/DiscoveryProbeTransportInterface.php`
- `src/Service/Discovery/DiscoveryFeedbackStoreInterface.php`
- `src/Service/Discovery/Libsource/Log/LibsourceOperatorEventLogStoreInterface.php`
- `src/Service/Discovery/Operations/DiscoveryOperationEventLogStoreInterface.php`
- `src/Service/Discovery/RateLimit/DiscoveryRateLimitStoreInterface.php`
- `src/Service/Discovery/Rebuild/DiscoveryRebuildEvidenceStoreInterface.php`
- `src/Service/Discovery/Source/Repository/DiscoverySourceRecordRepositoryInterface.php`
- `src/Service/Discovery/Support/DirectoryBackedFamilyManagementActionServiceInterface.php`

## Retired legacy subscriber paths

- `src/EventSubscriber/DiscoveryEndpointSecuritySubscriber.php`
- `src/EventSubscriber/DiscoveryMutationRequestHardeningSubscriber.php`
- `src/EventSubscriber/DiscoveryRateLimitSubscriber.php`
- `src/EventSubscriber/DiscoveryRequestCorrelationSubscriber.php`
- `src/EventSubscriber/DiscoveryResponseSecurityHeadersSubscriber.php`

## Safety contract

The apply script moves only the explicit paths listed above into:

```text
.patch-backups/discovering_wave8_residual_legacy_paths/
```

If a listed path is already absent, the script skips it. This keeps the patch compatible with repositories where wave 2 and wave 3 already retired the files.

## Runtime impact

No service behavior is intentionally changed in this wave. The purpose is to eliminate duplicate/autoloadable legacy definitions that can hide canonical layer adoption problems.

## Verification

```bash
php -l tools/discovering_canon_audit.php
php tools/discovering_canon_audit.php
```

Expected posture after successful retirement:

```text
wave8 legacy service interface files: 0
wave8 legacy event subscriber files: 0
```

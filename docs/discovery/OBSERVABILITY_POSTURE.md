# Discovering Observability Posture

This document records the current Discovering observability and operation-event surface.

## Request correlation

Request correlation is owned by:

- `src/Subscriber/Discovery/DiscoveryRequestCorrelationSubscriber.php`

The subscriber uses constants from `DiscoveryOperationLogger` and propagates the request ID through HTTP response headers.

## Operation logger

Operation logging is owned by:

- `src/Service/Discovery/Operations/DiscoveryOperationLogger.php`

It records HTTP/application operation events with request ID, channel, operation, status and structured context.

## Operation event contract

The operation-event payload is:

- `src/Dto/Discovery/DiscoveryOperationEvent.php`

The persistence model is:

- `src/Entity/Discovery/DiscoveryOperationEventEntity.php`

The service contract is:

- `src/ServiceInterface/Discovery/Operations/DiscoveryOperationEventLogStoreInterface.php`

## Store implementations

Operation-event store implementations are under `src/Service/Discovery/Operations/`:

- `ConfigurableDiscoveryOperationEventLogStore`
- `DoctrineDiscoveryOperationEventLogStore`
- `FileDiscoveryOperationEventLogStore`

Serialization is handled by:

- `DiscoveryOperationEventJsonSerializer`

## Runtime configuration

Operation log storage is configured in `config/services/discovery.yaml` through:

- `app.discovery.default_operation_log_path`
- `app.discovery.default_operation_log_backend`
- `APP_DISCOVERY_OPERATION_LOG_PATH`
- `APP_DISCOVERY_OPERATION_LOG_BACKEND`

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

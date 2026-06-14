# Discovery subscribers

This directory contains Discovering Symfony event subscribers.

## Current subscribers

- `DiscoveryEndpointSecuritySubscriber`
- `DiscoveryMutationRequestHardeningSubscriber`
- `DiscoveryRateLimitSubscriber`
- `DiscoveryRequestCorrelationSubscriber`
- `DiscoveryResponseSecurityHeadersSubscriber`

## Rules

- These classes are Symfony event subscribers, not domain event payloads.
- Event payload DTOs belong under `src/Dto/Discovery`.
- Event-log persistence models belong under `src/Entity/Discovery`.
- Operation/operator event stores belong under service or service-interface layers.

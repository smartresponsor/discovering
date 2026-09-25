# Discovering Canonization Wave 3 — Subscriber Layer Alignment

## Scope

Wave 3 aligns Symfony event subscribers with the platform source-tree canon.
Subscribers are type-identifiable infrastructure classes and therefore belong under
`src/Subscriber`, not under the legacy `src/EventSubscriber` folder name.

## Touched structural move

```text
src/EventSubscriber/*.php
→ src/Subscriber/Discovery/*.php
```

The class suffix remains `Subscriber`, because these classes implement Symfony's
`EventSubscriberInterface` and should remain recognizable as Symfony subscribers.

## Namespace move

```text
App\EventSubscriber\*
→ App\Subscriber\Discovery\*
```

## Explicit service wiring

The explicit service configuration for `DiscoveryEndpointSecuritySubscriber` was updated
to the new namespace. The remaining subscribers continue to be discovered through the
normal `App\` resource with autowiring/autoconfiguration.

## Legacy retirement policy

The touched legacy files in `src/EventSubscriber` are retired by the apply script into
`.patch-backups/discovering_wave3_subscriber_layer/` before the new files are overlaid.
No repository-wide deletion or cumulative overwrite is used.

## Post-apply checks

```bash
php -l src/Subscriber/Discovery/DiscoveryEndpointSecuritySubscriber.php
php -l src/Subscriber/Discovery/DiscoveryMutationRequestHardeningSubscriber.php
php -l src/Subscriber/Discovery/DiscoveryRateLimitSubscriber.php
php -l src/Subscriber/Discovery/DiscoveryRequestCorrelationSubscriber.php
php -l src/Subscriber/Discovery/DiscoveryResponseSecurityHeadersSubscriber.php
php bin/console lint:container
vendor/bin/phpunit
```

## Next recommended wave

Wave 4 should focus on the DTO/ValueObject shape decision: keep DTO as transport/input-output
contracts and move immutable domain concepts into `ValueObject`, without touching Doctrine
Entity exceptions.

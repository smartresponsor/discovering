# Discovering Canonization Wave 27 — Runtime / Quality Entrypoint Taxonomy

Wave 27 documents and machine-checks repository-local quality entrypoints before a separate runtime proof phase.

## Scope

Touched files:

- `tools/MANIFEST.md`
- `tools/security_preflight.php`
- `docs/discovery/CANONIZATION_WAVE27_RUNTIME_QUALITY_ENTRYPOINT_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Functional fix

`tools/security_preflight.php` now checks canonical subscriber paths under:

- `src/Subscriber/Discovery/DiscoveryResponseSecurityHeadersSubscriber.php`
- `src/Subscriber/Discovery/DiscoveryEndpointSecuritySubscriber.php`
- `src/Subscriber/Discovery/DiscoveryRateLimitSubscriber.php`
- `src/Subscriber/Discovery/DiscoveryRequestCorrelationSubscriber.php`

The previous `src/EventSubscriber/...` paths were stale after the subscriber layer migration.

## Canonical posture

Quality and runtime entrypoints are centralized through:

- Composer scripts in `composer.json`
- local tools in `tools/`
- PHPUnit config in `phpunit.xml.dist`
- PHPStan config in `phpstan.neon.dist`
- PHP-CS-Fixer config in `.php-cs-fixer.dist.php`

## Non-goals

- No full runtime proof.
- No Composer dependency changes.
- No vendor-dependent QA execution.
- No PHPUnit/PHPStan baseline changes.
- No destructive cleanup.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
php tools/security_preflight.php
php tools/runtime_preflight.php
```

Expected runtime/quality entrypoint counter:

```text
wave27 runtime quality entrypoint taxonomy findings: 0
```

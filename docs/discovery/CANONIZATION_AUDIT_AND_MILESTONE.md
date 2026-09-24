# Discovering Canonization Audit and Milestone

Date: 2026-04-30
Base archive: `DiscoveringThu.zip`
Scope: factual static audit of the provided current repository slice only.

## Executive summary

Discovering already contains a meaningful Symfony 8 / PHP 8.4 application surface: commands, controllers, DTOs, forms, Doctrine entities, services, service interfaces, Twig templates, resources, tests, QA tools, and documentation. The problem is not lack of code. The problem is canonical shape drift: class forms are not consistently encoded in names, several interface contracts live inside service implementation folders, event subscribers are still under `src/EventSubscriber`, many non-entity layers repeat the `Discovery` domain bucket under the layer root, and Doctrine is present but not yet expressed as an entity-first lifecycle with migrations and repository boundaries.

This document intentionally does not prescribe a full repository overwrite. It defines a touched-file, wave-based cleanup path.

## Current factual inventory

- Total files in the provided slice: 352.
- PHP files: 248.
- PHP files under `src/`: 168.
- Top-level directories: `bin`, `config`, `docs`, `report`, `resources`, `src`, `templates`, `tests`, `tools`.
- Main namespace in `composer.json`: `App\\ => src/`.
- Doctrine ORM mapping currently points to `src/Entity` with prefix `App\\Entity`.
- Doctrine entities currently found under `src/Entity/Discovery`: 7.
- Doctrine migrations directory was not found in the provided slice.
- Current table names use the `discovery_` prefix, which matches the component/domain table-prefix canon.

## Canonical decisions for this repository slice

1. Keep the default Symfony `App\\...` namespace for this repository unless explicitly changed later.
2. Keep entity classes under `src/Entity/Discovery` for now, because entity-scoped domain folders may be acceptable and the current Doctrine mapping supports this shape.
3. Do not introduce ports/adapters or `/src/Domain`.
4. Do not overwrite the repository with a cumulative snapshot.
5. Apply only touched files and explicitly named retirements, with backup when retirement is needed.

## Main findings

### 1. Namespace is acceptable but layer paths are too domain-bucketed

The repository uses default Symfony `App\\...`, which is acceptable for this Discovering slice. However, almost every layer repeats a `Discovery` bucket:

- `src/Command/Discovery/...`
- `src/Controller/Discovery/...`
- `src/Dto/Discovery/...`
- `src/Form/Discovery/...`
- `src/Service/Discovery/...`
- `src/ServiceInterface/Discovery/...`
- `src/ValueObject/Discovery/...`
- `tests/*/Discovery/...`

This is partially readable, but it weakens layer identity because `Discovery` becomes a catch-all domain folder rather than a true class-form boundary. Entity folders are a possible exception. Service and interface folders require the most care.

### 2. Interfaces are mixed into implementation service folders

The following interface families currently live under `src/Service/Discovery/...` instead of a mirrored interface layer:

- `src/Service/Discovery/Diagnostics/DiscoveryProbeTransportInterface.php`
- `src/Service/Discovery/DiscoveryFeedbackStoreInterface.php`
- `src/Service/Discovery/Libsource/Log/DiscoveryLibsourceOperatorEventLogStoreInterface.php`
- `src/Service/Discovery/Operations/DiscoveryOperationEventLogStoreInterface.php`
- `src/Service/Discovery/RateLimit/DiscoveryRateLimitStoreInterface.php`
- `src/Service/Discovery/Rebuild/DiscoveryRebuildEvidenceStoreInterface.php`
- `src/Service/Discovery/Source/Repository/DiscoverySourceRecordRepositoryInterface.php`
- `src/Service/Discovery/Support/DiscoveryDirectoryBackedFamilyManagementActionServiceInterface.php`

This is the most obvious structural cleanup target. Contracts should not remain hidden among concrete implementations.

### 3. Subscriber layer name is not aligned with current layer canon

Subscribers currently live in `src/EventSubscriber`. For ecosystem consistency, this should be normalized in a later wave to `src/Subscriber`, with class names still ending in `Subscriber`.

Current files:

- `DiscoveryEndpointSecuritySubscriber.php`
- `DiscoveryMutationRequestHardeningSubscriber.php`
- `DiscoveryRateLimitSubscriber.php`
- `DiscoveryRequestCorrelationSubscriber.php`
- `DiscoveryResponseSecurityHeadersSubscriber.php`

### 4. Entity-first posture is partial, not complete

Doctrine entities exist and table prefixes are reasonable, but the component is not yet fully entity-first:

- no migrations directory is present;
- no Doctrine migrations package is declared;
- several stores still support file/ephemeral backends;
- service wiring makes Doctrine configurable rather than clearly primary;
- repository boundaries for entity persistence are not explicit as Symfony/Doctrine repository layers.

This does not mean all file stores must be deleted immediately. It means Doctrine should become the canonical primary path, while file/ephemeral paths should be explicitly classified as dev/test/fallback if retained.

### 5. DTO and ValueObject class-form naming needs a policy pass

Many DTOs are clear enough by suffix (`Result`, `Entry`, `Surface`, `Event`, `Query`, `Report`, `Summary`, `Plan`, `Record`, `Descriptor`, `Topology`, `Decision`, `Request`). Some names are semantically valid but not form-explicit enough for strict ecosystem scanning, for example `DiscoveryHitDTO`, `DiscoveryModeDTO`, `DiscoveryDocument`, and `DiscoveryScope`.

A later wave should decide whether to keep these as accepted forms or rename them to stricter forms such as `DiscoveryHitDto`, `DiscoveryModePreset`, `DiscoveryDocumentValue`, or `DiscoveryScopeValue`.

### 6. Root and support directories are not catastrophic, but they need ownership labels

The root is not as chaotic as some earlier slices: major directories are conventional. However, root-level manifests and report/docs/resources/tools overlap in responsibility. The cleanup should add ownership rules rather than delete broadly.

## Milestone: canonicalization and cleanup waves

### Wave 1 — Audit baseline and guardrail tool

Goal: establish a reproducible local audit surface without moving code yet.

Touched-file output:

- `docs/discovery/CANONIZATION_AUDIT_AND_MILESTONE.md`
- `tools/discovering_canon_audit.php`

Status: first patch wave.

### Wave 2 — Service contract extraction

Goal: move interface contracts out of `src/Service/Discovery/...` into mirrored `src/ServiceInterface/...` or a more exact canonical interface layer where needed.

Expected touched areas:

- `src/Service/Discovery/**/**Interface.php`
- `src/ServiceInterface/Discovery/...`
- `config/services.yaml`
- dependent `use` imports in services/tests

Retirement mode: explicit touched legacy interface files only, with backup.

### Wave 3 — Subscriber layer normalization

Goal: move `src/EventSubscriber/*Subscriber.php` to `src/Subscriber/...` and update namespaces/references.

Expected touched areas:

- `src/EventSubscriber/*.php`
- `src/Subscriber/...`
- tests if they import subscriber classes
- `config/services.yaml` only if explicit service IDs are used

Retirement mode: explicit touched legacy subscriber files only, with backup.

### Wave 4 — Entity-first Doctrine baseline

Goal: make Doctrine the canonical data path without destroying fallback code prematurely.

Expected work:

- introduce Doctrine migrations package/config if missing;
- add initial migration for existing `discovery_` tables;
- introduce repository layer where useful;
- classify file/ephemeral stores as fallback/dev/test in docs and service wiring;
- keep table prefix `discovery_`.

### Wave 5 — Repository/source-provider layer clarification

Goal: separate source providers, record repositories, JSON support, and Doctrine repositories by class form.

Expected work:

- clarify whether `DiscoverySourceRecordRepositoryInterface` belongs in `RepositoryInterface`, `ServiceInterface`, or remains a service-level contract;
- rename/move support encoder/decoder classes if necessary;
- preserve source provider tags.

### Wave 6 — DTO and ValueObject naming pass

Goal: make class form explicit and scanner-friendly.

Expected work:

- decide accepted DTO suffixes;
- rename ambiguous DTOs/value objects only where the gain is real;
- update tests/imports/templates as touched references.

### Wave 7 — Controller/command/form surface cleanup

Goal: ensure public, management, CLI, and form surfaces are separate and name-explicit.

Expected work:

- verify route names and controller namespaces;
- make management controllers consistently prefixed/suffixed;
- keep Symfony attribute routing;
- avoid non-Symfony custom architecture.

### Wave 8 — Root/docs/resources ownership pass

Goal: reduce root-level confusion without deleting useful project knowledge.

Expected work:

- move or index generated docs/report artifacts where appropriate;
- keep Antora docs stable;
- clarify `resources/discovery` as seed/source records or fixtures;
- avoid broad cleanup scripts.

### Wave 9 — Test namespace/path alignment

Goal: keep tests synchronized after structural moves.

Expected work:

- mirror moved production classes in test paths where useful;
- update imports;
- preserve existing test behavior and suites.

## Next recommended patch

Start with Wave 2: service contract extraction. It is the highest-value, lowest-concept-risk cleanup because the current violation is factual and local: interface contracts are mixed into implementation folders. This can be done with a touched-files patch and explicit backup retirements of only the old interface paths.

# Discovering entity-first migration retirement

## Scope

Current platform slice: `www-clean-20260610-170157(1).zip`.
Legacy donor slice: `Entity-src(6).zip`.

## Retired schema-first sources

- `Discovering/migrations/**`

These files mirrored the current Doctrine mapping and are no longer the source of truth for the model.

## Current migration coverage

The retired migration tables are covered by existing Doctrine entities:

- `discovery_feedback` -> `DiscoveryFeedbackEntity`
- `discovery_index_alias` -> `DiscoveryIndexAliasEntity`
- `discovery_index_document` -> `DiscoveryIndexDocumentEntity`
- `discovery_operation_event_log` -> `DiscoveryOperationEventEntity`
- `discovery_rate_limit_bucket` -> `DiscoveryRateLimitBucketEntity`
- `discovery_rebuild_evidence` -> `DiscoveryRebuildEvidenceEntity`
- `discovery_libsource_operator_event_log` -> `DiscoveryLibsourceOperatorEventEntity`

## Restored legacy concept

Old monolith `Entity/Search/SearchLog.php` was not represented as a Discovering persistence model. It has been normalized as:

- `DiscoverySearchLogEntity`

`customerId` was renamed to `customerReference` to avoid a hard Doctrine dependency on a customer aggregate outside Discovering.

## Objecting decision

`SearchLog` inherited old `ObjectAuditTrait`. The new `DiscoverySearchLogEntity` uses Objecting embeddable traits instead:

- `ObjectIdentityEmbeddableTrait`
- `ObjectAuditEmbeddableTrait`

Existing Discovering entities were not force-converted to Objecting because their columns are already runtime/event lifecycle fields and the retired migration mirrors those tables exactly. No generic Objecting fields were duplicated into them.

## Repository canon

Repository interfaces and concrete Doctrine repositories were added for all Discovering entities. Existing entity metadata now points to its repository class through `repositoryClass`.

# Discovery rebuild evidence v1

This repository records rebuild evidence as a file-backed operational artifact.

## Purpose

The evidence trail makes every rebuild auditable and explicitly reports whether the current backend is ready for zero-downtime cutover.

## Current baseline

- evidence is exported from `GET /management/discovery/rebuilds/export`
- every rebuild summary carries an `evidenceId`
- `deploymentMode` is explicit in every summary
- backends that support staged promotion may report `deploymentMode: staged_alias_swap`
- backends that do not support staged promotion continue to report `deploymentMode: in_place`
- `zeroDowntimeReady` is backend- and deployment-mode-dependent rather than globally fixed

## Summary fields

- `evidenceId`
- `resource`
- `rebuildMode`
- `backendName`
- `deploymentMode`
- `zeroDowntimeReady`
- `startedAt`
- `finishedAt`
- `candidateDocumentCount`
- `indexedDocumentCount`
- `skippedDocumentCount`
- `indexedCountsByResource`
- `stagedIndexes`
- `aliasSwapApplied`

## Operational intent

The evidence contract is now stable enough to represent both in-place rebuilds and staged alias-swap promotions without changing shape. For SQLite-backed staged rebuild support, see `docs/discovery/STAGED_REBUILD_V1.md`.

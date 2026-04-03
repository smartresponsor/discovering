# Discovery rebuild evidence v1

This repository records rebuild evidence as a file-backed operational artifact.

## Purpose

The evidence trail makes every rebuild auditable and explicitly reports whether the current backend is ready for zero-downtime cutover.

## Current baseline

- `deploymentMode`: `in_place`
- `zeroDowntimeReady`: `false`
- evidence is exported from `GET /management/discovery/rebuilds/export`
- every rebuild summary carries an `evidenceId`

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

## Operational intent

The current wave does **not** claim real alias-swap cutover support. Instead, it formalizes rebuild evidence so future staged-index and alias-swap work can be added without changing the evidence shape.

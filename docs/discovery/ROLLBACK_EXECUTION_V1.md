# Discovery rollback execution v1

## Scope

This document describes the first executable rollback primitive for the Discovering component.

## What is executable now

- the current rollback plan can be derived from rebuild evidence history
- a guarded management mutation endpoint can execute alias rollback promotion
- a CLI command can execute the same rollback path
- execution is blocked when the rollback plan is not ready or when requested evidence ids no longer match the latest rollback posture

## Execution primitives

- `discovering:rollback:execute --current=<evidence> --target=<evidence>`
- `POST /management/discovery/rollback/execute`

## Safety posture

Rollback execution remains intentionally narrow:

- only the current rollback plan target may be promoted
- evidence ids can be supplied to prevent stale-operator promotion
- management token protection still applies to the HTTP path
- blocked execution returns a structured conflict response instead of silently mutating aliases

## Current limitation

This wave executes alias rollback only. It does not recreate prior documents or infer historical content state. It assumes the earlier physical index still exists and remains a valid rollback target.

# Discovery Shared-State Topology v1

This document describes the first shared-state / distributed-readiness reporting layer for the Discovering component.

## Purpose

The repository now exposes a topology report for the main mutable discovery state surfaces:

- discovery SQLite index
- discovery feedback SQLite store
- discovery operation log
- discovery rebuild evidence log
- libsource operator event log

The topology report is intentionally conservative. It does **not** claim multi-replica readiness just because a path can be moved outside `var/discovery`.

## Current posture

The current implementation supports environment-level path overrides for mutable state, but the stores remain file and SQLite oriented.

That means:

- paths can be externalized
- state can be moved to a shared filesystem location
- but the component is still **not** considered distributed write-ready

## Why distributedReady remains false

### SQLite stores

The discovery index and feedback stores are SQLite-backed. They are treated as single-node oriented and not promoted to multi-replica write-safe state even when their files are placed on shared storage.

### JSON file stores

The operation log, rebuild evidence log, and libsource event log are JSON file-backed. They use read-modify-write mutation semantics and are therefore not treated as distributed coordination stores.

## What this seam is for

This topology layer gives operators and future platform work a stable place to inspect:

- which mutable paths are still local
- which paths have been externalized
- which stores still block multi-replica readiness

## Future direction

A stronger distributed posture would require replacing or augmenting current state stores with more coordination-safe backends, for example:

- Redis-like rate/coordination state
- relational stores for evidence and logs
- backend-managed index aliases on a replicated search engine

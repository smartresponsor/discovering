# Discovery staged rebuild baseline v1

The discovery rebuild path now exposes a staged alias-swap seam for adapters that can keep logical indexes stable while promoting a newly built staged index.

## Current behavior

- `deploymentMode=auto` chooses `staged_alias_swap` only when the adapter reports staged-rebuild capability and the rebuild target is `global`.
- Partial resource rebuilds remain `in_place` because replacing the `global` logical index with a resource-partial corpus would be unsafe.
- SQLite FTS now maintains a lightweight alias map so logical indexes like `global` and `briefing` can be promoted to staged physical indexes without changing the query-side contract.

## Evidence shape additions

Rebuild evidence now includes:

- `stagedIndexes`
- `aliasSwapApplied`

These fields are additive to the existing rebuild summary contract.

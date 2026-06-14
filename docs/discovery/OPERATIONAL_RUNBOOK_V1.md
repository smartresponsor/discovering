# Discovery Operational Runbook v1

This runbook describes the minimum operator loop around discovery rebuilds, exports, staged cutover, and rollback planning.

## Core operator sequence

1. Review management overview at `/management/discovery`.
2. Export current state topology, operations, and rollback posture.
3. Run `app:discovery:rebuild` for the intended scope.
4. Confirm rebuild evidence and staged promotion outcome.
5. Perform smoke checks against `/api/discovery` and operator exports.
6. If behavior regresses, use the rollback plan export to recover the previous physical index target.

## Pre-cutover checks

- management token and API write token are present
- state topology is understood and mutable stores are reachable
- recent operation log shows no unresolved rebuild failures
- current rollback plan is either `plan_ready` or explicitly understood as not available

## Post-cutover checks

- discovery query smoke checks return expected seeded/known results
- rebuild evidence export contains the latest evidence id
- rollback export points to the new current evidence and a previous candidate
- no unexpected `429` or security denials occur on expected operator paths

## Recovery notes

Rollback planning is available now. Rollback execution is still an operator action and is not yet an automatic promotion command.


## Rollback execution

When rollback posture is ready, operators can now execute rollback with `app:discovery:rollback:execute --current=<evidence> --target=<evidence>` or the guarded management mutation endpoint.

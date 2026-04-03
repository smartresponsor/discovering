# Discovering status report — 2026-04-03

## Scope

This report reflects the current local repository state built exclusively on the current slice.
It summarizes completed waves, direct unfinished items, and the next recommended execution order.

## Repository status at report time

- branch: `codex/local-session-20260403-discovering`
- working tree: clean
- note: a large 125-file modified tranche was inspected and confirmed to be CRLF/EOL churn only; it was intentionally discarded and not committed.

## Completed waves in this session

1. `7983f54` — repair discovery contracts and management overview
2. `1369304` — add symfony bootstrap baseline
3. `92948d9` — add functional discovery test contour
4. `2e59670` — refresh discovery unit contour for new contracts
5. `f1afef8` — harden discovery management and api write surfaces
6. `5e0ad2a` — add discovery request correlation and operation log baseline
7. `b0f9479` — version discovery api responses and add v1 aliases
8. `64ddacf` — codify discovery api contract metadata and tests
9. `7825512` — add discovery rebuild evidence baseline
10. `da179b6` — add staged rebuild alias swap seam
11. `f6bab4c` — add discovery behavioral scenario tests
12. `2b2c85a` — add ci runtime verification baseline
13. `263baac` — document discovery threat model and harden response headers
14. `1d7f16e` — add discovery state topology and shared-state seam
15. `dc3330a` — add discovery rate limiting baseline
16. `e97378b` — add discovery rollback planning baseline

## Direct unfinished items

### 1. Runtime installability is designed but not yet proven in this environment

The repository now has a Symfony bootstrap baseline, PHPUnit suites, CI wiring, and clearer contracts. However, this environment did not contain installed `vendor/` dependencies, so a real `composer install`, `bin/console`, and `phpunit` execution was not confirmed here.

**Impact:** architecture and test contours are much stronger than before, but runtime proof is still pending.

### 2. Shared-state externalization is now inspectable, but distributed readiness still remains false

The repository can now describe mutable discovery state paths and can externalize them through environment-level path overrides. However, the current state backends remain SQLite and JSON-file oriented, so the component is still not treated as multi-replica write-ready.

**Impact:** the platform seam is clearer, but a true distributed posture still requires stronger coordination stores than shared files.

### 3. Rollback execution now exists, but it remains intentionally narrow

The repository now exposes rollback posture from rebuild evidence and can execute guarded alias rollback promotion through dedicated CLI and management mutation paths. However, rollback still assumes that the earlier physical index exists and remains a valid promotion target; it does not recreate historical content state.

**Impact:** rollback is now operationally actionable, but it is still alias-level execution rather than full historical state restoration.

### 4. Full install-and-run verification remains the strongest next proving step

The current codebase is now ready for a proving wave centered on installation, real Symfony boot, functional execution, and CLI smoke checks.

**Impact:** this is the shortest path from "architecturally advanced" to "operationally believable".

## Resolved in this report wave

- report layer now exists in the repository instead of an empty `report/` directory only
- the previous documentation contradiction between rebuild evidence and staged rebuild support was aligned
- format-only CRLF churn was explicitly identified and discarded instead of being allowed into history
- rate limiting now exists for query, write, and management-mutation discovery paths instead of remaining only a documented security gap
- rollback posture, guarded execution, CLI commands, and operator runbooks now exist instead of rollback clarity remaining only implicit

## Recommended next execution order

1. Prove installability with `composer install`
2. Run PHPUnit suites and fix runtime regressions
3. Run `bin/console` command smoke checks
4. Decide whether shared-state overrides are enough for the target deployment, or whether discovery state and throttling must move to stronger coordination backends
5. If needed later, broaden rollback beyond alias promotion toward richer historical-state recovery semantics

## Current architectural verdict

`Discovering` is no longer just a promising slice. It now has a materially stronger contract, bootstrap, security, test, observability, and rebuild posture. The main remaining gap is not conceptual architecture; it is runtime proof, operator playbooks, and the eventual move from local mutable stores toward stronger shared coordination backends if multi-replica deployment becomes a target.


Update: rollback execution is now implemented as an alias-promotion primitive and is no longer manual-only when the rollback plan is ready.

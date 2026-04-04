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
16. `546782f` — add discovery rollback planning baseline
17. `6a36629` — prove runtime boot on current slice
18. `734d5b9` — add shared coordination backends for discovery operator stores
19. `c4f4249` — add shared feedback coordination seam
20. `e335ae8` — add shared discovery index backend seam
21. `26b1b5d` — add discovery platform diagnostics surface

22. `8c96856` — add discovery backend reachability probes
23. `14bfc5b` — harden discovery test temp filesystem discipline
24. `847e45c` — harden directory-backed repository test resource fixtures
25. `6cbb031` — ignore generated symfony config reference
26. `b6ca96f` — add discovery health interpretation surfaces
27. `117eafb` — finish discovery temp test cleanup on shared filesystem helpers
28. `0c9e4e5` — harden functional discovery test harness
29. `a78139c` — consolidate functional discovery HTTP assertions
30. `9fee68c` — harden discovery management functional scenarios
31. `adaf4d3` — consolidate discovery management export and mutation test helpers

## Direct unfinished items

### 1. Runtime boot is now proven, but full test execution still depends on environment completeness

The current slice now boots through real Symfony console paths: `bin/console list --raw`, `lint:container`, and `debug:router` were proven after boot blockers were removed. However, full PHPUnit execution still depends on the active environment having the required PHP extensions and an aligned installed `vendor/` toolset.

**Impact:** runtime credibility is materially stronger than before, but full test execution still remains the next proving step.

### 2. Shared-state externalization is now inspectable, and backend reachability probes now exist

The repository can now describe mutable discovery state paths and can externalize them through environment-level path overrides. Rate limiting, feedback learning, operation history, rebuild evidence, and libsource event history can all move to PDO-backed coordination tables. The discovery index also supports backend selection between local SQLite FTS and shared Meilisearch service mode. In addition, dedicated CLI and management probe surfaces now exist for active Meilisearch and PDO-backed coordination targets, so reachability can be checked on demand instead of being inferred only from configuration. Distributed readiness still depends on the chosen combination of index and coordination backends rather than being assumed automatically.

**Impact:** the platform seam is materially stronger than before, and distributed readiness can now be expressed honestly instead of remaining permanently false.

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
- discovery index backend selection now exists between local SQLite FTS and shared Meilisearch service mode
- platform diagnostics now exists as a CLI, management export, and overview surface for adapter capability, topology posture, and rollback readiness
- explicit backend reachability probes now exist as a dedicated CLI and management export for Meilisearch and PDO-backed coordination targets
- file- and SQLite-heavy behavioral/unit tests now share a dedicated temporary filesystem support layer instead of ad hoc `sys_get_temp_dir()` + manual cleanup patterns
- briefing/playbook directory-backed repository and management unit tests now use shared project/resource fixture helpers with automatic teardown instead of hand-written `mkdir`/`unlink`/`rmdir` sequences
- functional discovery web tests now reset configured mutable state paths from test DI parameters and share common JSON/body helpers instead of hard-coded filenames and repeated inline payload decoding
- management/API functional discovery scenarios now share central request/rebuild/rollback helpers instead of duplicating tokenized request wiring and multi-step rollback setup inline in each test
- JSON response schema metadata now preserves payload-specific `schemaFamily`/`schemaVersion` overrides in response `meta`, while response headers remain fixed to the shared envelope contract

## Recommended next execution order

1. Run PHPUnit suites and fix remaining runtime regressions
2. In a deployment target, decide whether discovery index should remain SQLite or switch to Meilisearch service mode
3. If needed later, broaden rollback beyond alias promotion toward richer historical-state recovery semantics

## Current architectural verdict

`Discovering` is no longer just a promising slice. It now has a materially stronger contract, bootstrap, security, test, observability, and rebuild posture. The main remaining gap is not conceptual architecture; it is runtime proof, operator playbooks, and the eventual move from local mutable stores toward stronger shared coordination backends if multi-replica deployment becomes a target.


Update: rollback execution is now implemented as an alias-promotion primitive and is no longer manual-only when the rollback plan is ready.


Update: rate limiting, operation history, rebuild evidence, libsource event history, and feedback learning now all support optional stronger coordination backends. Discovery index backend selection now also exists between SQLite FTS and Meilisearch service mode.

Update: `config/reference.php` is treated as generated Symfony app reference output and is now ignored instead of left as recurring untracked noise.


Update: platform diagnostics and backend probes now expose operator-friendly health interpretation fields (`postureStatus`, `riskLevel`, `overallStatus`, `recommendedAction`) instead of leaving operators with only raw topology and probe counters.


Update: temp filesystem support now exposes dedicated sqlite/json path helpers, and remaining file-backed unit tests now rely on automatic temp-root teardown instead of manual `unlink()` cleanup.


Update: discovery JSON response metadata now distinguishes shared envelope headers from payload-specific `meta.schemaFamily`/`meta.schemaVersion`, so management exports can advertise their own contracts without losing the common outer envelope.


Update: management and API functional discovery tests now share central scenario helpers for tokenized requests, rebuild sequences, rollback plan export, and rollback execution payloads instead of repeating inline setup across tests.


Update: management functional tests now share central export/mutation/page helpers, so tokenized management requests and JSON-envelope assertions no longer need to be repeated inline across overview, rebuild, rollback, platform, and state-topology scenarios.

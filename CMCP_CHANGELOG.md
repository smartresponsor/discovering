# CMCP Orchestration Journal

## engine-20260911152914-discovering-6af99f

### Iteration 1 — reconnaissance and baseline

- Workspace: `D:\PhpstormProjects\www\Discovering`; branch `master`; HEAD `133023ac7b3780169ef64f879181a1da4e0c41ab`; upstream `origin/master`; local branch is 2 commits ahead.
- Pre-existing untracked paths preserved: `.gating/`, `AGENTS.md`, `tools/migrate_component_namespace.php`.
- Read target `AGENTS.md`, `README.md`, `composer.json`, Symfony kernel/bundle wiring, source/test topology, and current Composer scripts.
- Read required dependency contour contracts from Objecting, Cruding, Viewing, and Interfacing; read Gating executable contract material and Canonization normative architecture rules.
- Canonization rules consulted: Canon000, Canon001, Canon002, Canon003, Canon004, Canon007, Canon008, Canon009, Canon012, Canon018.
- Target mapping: preserve `App\\Discovering\\ => src/` per Canon018; keep role-first trees; migrate DTO transport types toward `src/DTO/` with `DTO` suffix per Canon003; remove/replace architectural `Adapter` taxonomy where it is a role bucket; keep mirrored Service/ServiceInterface and Repository/RepositoryInterface trees; ensure foreign runtime dependencies are explicit in Composer.
- Current concrete debt: `composer validate --strict` fails on exact Doctrine constraints and unbounded `objecting/object`; mandatory Objecting/Cruding/Viewing/Interfacing dependency contour is incomplete in target Composer metadata; DTO and Adapter topology is non-canonical.
- RC-critical workstream: restore Composer/package reproducibility first, then close structural canon violations in bounded waves with tests/gates after each wave.
- Growth workstream (post-RC): evaluate semantic/vector hybrid retrieval and relevance tuning only after lexical/index lifecycle, explainability, diagnostics, rollback, and API contracts are green.
- Planned gates: Composer strict validation, runtime preflight, security/docblock/PHP lint, PHPStan/CS, PHPUnit suites, Symfony console/container checks, and available Gating checks.

### Market baseline

- Mature discovery/search products converge on tunable lexical relevance, filters/facets, explainable scoring, safe index lifecycle, operational diagnostics, and increasingly hybrid lexical/vector retrieval.
- RC does not depend on speculative semantic/vector growth; current priority is deterministic package/runtime integrity and canonical component structure.

## 2026-09-14 — RC hardening and canon closure

### Reconnaissance and normative mapping

- Re-read the current Discovering documentation, manifests, Composer/Symfony configuration, source, tests, CI/gates, and runtime evidence surfaces before patching.
- Re-read the required application contour from Objecting, Cruding, Viewing, and Interfacing; additionally verified Collectioning and Tabling after applying the standalone-application baseline.
- Read Gating as executable enforcement and Canonization as the normative textual source. Material rules consulted/applied in this pass include Canon000, Canon001, Canon002, Canon007, Canon008, Canon009, Canon010, Canon017, Canon018, Canon019, Canon020, Canon021, Canon022, Canon023, Canon024, Canon025, Canon026, Canon027, Canon028, Canon029, Canon030, Canon031, Canon032, Canon033, Canon036, Canon038, Canon039, Canon041, Canon043, Canon044, and Canon045.
- Target mapping: keep `App\\Discovering\\ => src/`; use technical-role trees and mirrored Service/ServiceInterface contracts; prohibit architectural Port/Adapter vocabulary; keep generic CRUD ownership in Cruding; use Objecting's current `uuid`/`slug` embedded property mapping while preserving its public identity API; declare the full standalone dependency baseline directly; keep development sibling path repositories separate from a packaged/VCS-only production manifest; and provide executable browser/UI tooling locally.

### RC-critical implementation

- Replaced the active discovery `Adapter` role with `Backend` across implementation, interfaces, DI, diagnostics, tests, manifests, current docs, and the repository-local canon audit. No compatibility Adapter wrappers were added.
- Repaired Discovering's Objecting RC2 consumer assumptions: Doctrine metadata assertions now use `objectIdentity.uuid` / `objectIdentity.slug`, and isolated tests use fresh attribute metadata to avoid stale local-path dependency caches.
- Registered/configured Symfony SecurityBundle for the standalone host so Interfacing templates can safely resolve `app.user`; retained a no-authentication firewall because Discovering's existing token/security subscribers remain the application access policy.
- Restored business UI rendering through Viewing's supported explicit producer-template contract: public discovery uses semantic operation `search`, management uses `overview`, and both declare their existing `@Discovering/...` templates without patching Viewing or Interfacing.
- Repaired the PHP CS Fixer gate itself (`Finder::name()` and consistent risky-fixer allowance), then applied the repository formatter and re-ran the check.
- Closed Canon022 by declaring Collectioning, Tabling, Cruding, Viewing, Interfacing, Objecting, EasyAdmin, and the now-used SecurityBundle directly and registering the required Symfony bundles.
- Closed Canon024 with a validated `composer.prod.json` using packaged/VCS resolution only; no sibling `path` or symlink repositories are present in the production manifest.
- Closed Canon041 with `symfony/test-pack`, Panther, repository-local Playwright, `package-lock.json`, Playwright config, and an executable `/discovery` browser smoke test. Headless Playwright uses a normal browser User-Agent so it exercises Viewing's intended human HTML path without weakening production bot classification.

### Verification

- `composer validate --strict`: PASS.
- `composer run-script validate:prod`: PASS (`composer.prod.json is valid`).
- `composer run-script test:all`: PASS — Unit 102/486, Contract 7/96, Behavioral 3/20, Functional 27/404 (139 tests, 1006 assertions total across suites).
- `composer run-script analyse`: PASS.
- `composer run-script lint:cs`: PASS after formatter closure.
- Changed-file PHP lint: PASS across the inspected changed/untracked PHP set.
- `verify:security`, `verify:docblocks`, `verify:runtime-preflight`, `verify:test-runtime`, `verify:console`, `lint:container`, `verify:structural-closure`, and `verify:console-container-evidence`: PASS in the current implementation track; console/container were rechecked after bundle-baseline expansion.
- RC diagnostic after the canon/dependency/tooling changes: `rc_diagnostic_green`; no hard blockers, only two existing TODO-marker warnings.
- Playwright smoke test against isolated Discovering runtime port 8123: PASS (1 test). Port 8000 was intentionally not used because a sibling Complying process occupied it.

### Residuals and release hygiene

- No known functional or hard-canon RC blocker remains from this implementation track.
- Two non-blocking diagnostic warnings remain for TODO/FIXME markers in `src/Form/Discovery/DiscoverySearchType.php` and `templates/management/discovery/libsource_log.html.twig`; they are not current hard failures.
- Growth remains post-RC: richer faceting/authority/relevance semantics, hybrid lexical/vector retrieval, and further discovery UX/API maturity must not be mixed into RC correctness work.
- Git integration must preserve the pre-existing dirty baseline. Do not create a mixed commit that silently absorbs unrelated user changes; final staging/commit/push is permitted only if the current work can be factually isolated.
- Final acceptance rerun: `composer ci` PASS after excluding generated `config/reference.php` from the CS source boundary; `composer run-script validate:prod` PASS; `npm test` / Playwright PASS; final RC diagnostic remains `rc_diagnostic_green` with the same two non-blocking TODO warnings.
- Initial Git snapshot before integration: protected `master`, HEAD `133023ac7b3780169ef64f879181a1da4e0c41ab`, upstream `origin/master`, ahead 2 / behind 0, dirty set 117 paths.
- The dirty tree was subsequently isolated by responsibility while preserving the three explicitly recorded baseline-untracked paths (`.gating/`, `AGENTS.md`, `tools/migrate_component_namespace.php`). Signed commits created for this RC workstream: `29e1461` (`refactor: replace discovery adapters with backends`), `e61c523` (`feat: harden discovering standalone runtime`), `c1847c6` (`test: close discovering rc acceptance`), `80a1646` (`style: normalize discovering php sources`), and `1a95084` (`build: unify php cs fixer configuration`).
- Formatter integration defect discovered post-commit: pre-commit used `.php-cs-fixer.php` while CI used `.php-cs-fixer.dist.php` with conflicting rule sets. Closed by making `.php-cs-fixer.dist.php` delegate to the canonical Symfony `.php-cs-fixer.php`, excluding generated `config/reference.php`, and preserving the original `src/tests/config` formatter scope so the baseline migration utility is not touched.
- Post-integration acceptance on committed HEAD: `composer ci` PASS and `npm test` / Playwright PASS. Current protected `master` HEAD before RC branch publication is `1a95084182b4b02f83974d49c20fc72c221aa479`, ahead 7 / behind 0; worktree contains only the three preserved baseline-untracked paths.

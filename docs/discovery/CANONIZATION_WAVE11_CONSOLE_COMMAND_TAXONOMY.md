# Discovering Canonization Wave 11: Console Command Taxonomy

## Purpose

Wave 11 aligns the Discovering CLI surface with the canonical Symfony-oriented command naming scheme.

The repository already had a mostly consistent command layer under `src/Command/Discovery`, but three operational commands still exposed `discovering:*` as their primary Symfony command names. This created a split operator surface beside the already canonical `app:discovery:*` commands.

## Canonical rule

Primary command names for this component use:

```text
app:discovery:*
```

Legacy `discovering:*` names may remain only as transition aliases for commands that already existed.

## Touched runtime files

```text
src/Command/Discovery/DiscoveryRebuildCommand.php
src/Command/Discovery/DiscoveryRollbackPlanCommand.php
src/Command/Discovery/DiscoveryRollbackExecuteCommand.php
src/Service/Discovery/Rebuild/DiscoveryRollbackPlanBuilder.php
```

## Touched tests and documentation

```text
tests/Unit/Discovery/DiscoveryRollbackPlanBuilderTest.php
docs/discovery/OPERATIONAL_RUNBOOK_V1.md
docs/discovery/ROLLBACK_EXECUTION_V1.md
src/Command/Discovery/MANIFEST.md
tools/discovering_canon_audit.php
```

## Runtime behavior

The canonical command names are now:

```text
app:discovery:rebuild
app:discovery:rollback:plan
app:discovery:rollback:execute
```

The old command names remain aliases:

```text
discovering:rebuild
discovering:rollback:plan
discovering:rollback:execute
```

This keeps operator compatibility while making the documented and generated CLI surface consistent.

## Audit posture

The audit tool now reports:

```text
wave11 command name findings
```

The expected value after this wave is:

```text
wave11 command name findings: 0
```

## Verification

```bash
php -l src/Command/Discovery/DiscoveryRebuildCommand.php
php -l src/Command/Discovery/DiscoveryRollbackPlanCommand.php
php -l src/Command/Discovery/DiscoveryRollbackExecuteCommand.php
php -l src/Service/Discovery/Rebuild/DiscoveryRollbackPlanBuilder.php
php -l tests/Unit/Discovery/DiscoveryRollbackPlanBuilderTest.php
php tools/discovering_canon_audit.php
php bin/console list app:discovery
php bin/console lint:container
```

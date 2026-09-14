# Discovery console commands

This directory contains Discovering CLI commands.

## Current command namespace

All Discovering commands use the `app:discovery:*` primary command-name family.

## Compatibility aliases

The following legacy aliases are currently accepted for compatibility:

- `discovering:rebuild`
- `discovering:rollback:plan`
- `discovering:rollback:execute`

## Rules

- New commands should use `#[AsCommand(name: 'app:discovery:...')]`.
- New commands should not introduce `discovering:*` as a primary name.
- Command classes should delegate business logic to services and remain thin CLI entrypoints.
- Command descriptions should be explicit enough for `bin/console list app:discovery`.

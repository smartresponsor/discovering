# Discovering command taxonomy

Console commands are kept in the dedicated Symfony command type layer.

## Canonical bucket

- `src/Command/Discovery/` contains Discovering CLI commands.

## Rules

- Command class files must end with `Command.php`.
- Command classes must use the `Command` suffix.
- Command namespaces must stay under `App\Command\Discovery`.
- Primary Symfony command names should use the `app:discovery:*` prefix.
- Legacy aliases may be retained only for compatibility and should not become the primary command name.

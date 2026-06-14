# File-backed discovery source repositories

This directory contains source-record repositories for file-backed discovery materials.

## Canonical distinction

These classes are service-layer source repositories, not Doctrine repositories.

They manage directory-backed or file-backed source records for discovery families such as projects, offerings, documents, playbooks and briefings.

## Rules

- Keep these classes under `src/Service/Discovery/Source/Repository/` while they are file/source-record services.
- Do not move them to `src/Repository/` unless they become Doctrine repositories.
- Class names may keep the `Repository` suffix because their responsibility is source-record collection access, but their namespace must make the non-Doctrine role explicit.
- Doctrine persistence repositories, when introduced, should be handled by a separate Repository/Persistence wave.

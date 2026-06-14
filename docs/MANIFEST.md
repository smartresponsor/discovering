# Discovering documentation taxonomy

Discovering documentation is split by publication role instead of being treated as one flat document bucket.

## Canonical buckets

- `docs/modules/ROOT/` is the Antora producer surface.
- `docs/discovery/` keeps detailed hand-written operational, architectural and canonicalization Markdown.
- `docs/manifests/` keeps durable producer-facing manifests moved out of repository root.
- `docs/generated/` is reserved for generated API and code-reference artifacts.

## Rules

- Antora pages should be stable entry points, not duplicate full Markdown runbooks.
- Generated artifacts must stay under `docs/generated/` and should not be hand-edited narrative documentation.
- Canonization wave records stay under `docs/discovery/` and are indexed from Antora through `canonization.adoc`.
- Repository-root documentation should stay limited to GitHub-facing files such as `README.md`.

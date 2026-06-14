# Manifest Index

Canonical repository-facing manifests now live in this directory instead of the repository root.

Read these first:

1. `BOUNDING_MANIFEST.md`
2. `ARCHITECTURE_MANIFEST.md`
3. `PRODUCT_MANIFEST.md`
4. `CODEX_CLI_PROMPT.txt`

Root-level `MANIFEST.txt` and `PATCH_MANIFEST.txt` were stale patch-delivery artifacts in this slice. They are intentionally retired by the Wave 7 touched-file script and kept only in the patch backup folder when present.

The repository root should stay focused on standard Symfony/project entry points such as `README.md`, `composer.json`, `phpunit.xml.dist`, `phpstan.neon.dist`, and the executable entry directories.

# Semantic DocBlock Baseline

The Discovering component uses semantic DocBlocks as a first-class documentation surface for both humans and tooling.

## Goals

- preserve descriptive top-level DocBlocks on classes, interfaces, traits, enums, and key public methods
- keep machine-oriented type details in the lower DocBlock section where they improve IDE, PHPUnit, PHPStan, or OpenAPI understanding
- avoid destructive auto-fixing that removes or rewrites narrative descriptions into type-only noise

## Coverage posture

Current baseline:

- `src/` class-like files are expected to have semantic class-level DocBlocks
- `tests/` class-like files are expected to have semantic class-level DocBlocks
- key public entrypoints and discovery contracts are documented with method-level semantic DocBlocks

The `tools/docblock_policy_check.php` guard verifies this baseline and is intended to fail CI when class-level semantic DocBlocks disappear.

## CS Fixer preservation rule

The repository keeps `.php-cs-fixer.dist.php` intentionally conservative:

- no destructive `phpdoc_to_comment`
- no automatic summary rewriting
- no superfluous-tag stripping that could collapse useful documentation
- no forced phpdoc alignment or trimming that would damage descriptive commentary

Type-oriented tag maintenance is allowed only when it does not damage the descriptive upper section of the DocBlock.

## Generated documentation surfaces

Semantic DocBlocks support, but do not replace, the generated documentation surfaces:

- OpenAPI / Swagger: `docs/generated/openapi/`
- generated PHP API reference surface: `docs/generated/doctum/`

Narrative and operational hand-written documentation remains under `docs/discovery/`.

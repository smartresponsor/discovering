# Generated PHP API Reference Surface

This directory is reserved for generated PHP API reference artifacts such as Doctum output.

Guidelines:

- hand-written narrative documentation remains under `docs/modules/ROOT/pages/` and `docs/discovery/`
- OpenAPI / Swagger artifacts remain under `docs/generated/openapi/`
- generated PHP code-reference artifacts should be emitted under `docs/generated/doctum/`
- the producer repository should not assemble or publish a complete documentation portal on its own

If a future CI or release job generates a PHP API reference surface, it should target this directory rather than mixing generated files into narrative Antora pages.

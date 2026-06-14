# Discovering Test Support Manifest

This directory contains reusable test-only infrastructure: factories, temporary filesystem helpers, entity-manager helpers and abstract support classes.

Support files are not production contracts. If a helper becomes useful for runtime behavior, move the concept into the appropriate `src/` layer and keep only test-specific fixture wiring here.

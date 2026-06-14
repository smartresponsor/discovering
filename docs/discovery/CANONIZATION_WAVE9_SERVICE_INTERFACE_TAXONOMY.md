# Discovering Canonization Wave 9 — ServiceInterface Contract Taxonomy

Wave 9 completes the service-contract taxonomy posture introduced by the earlier service contract extraction wave.

## Scope

Touched files only:

- `src/ServiceInterface/**/MANIFEST.md`
- `tools/discovering_canon_audit.php`
- this wave note

No runtime service, controller, command, entity, migration, or template behavior is changed.

## Canonical posture

`src/ServiceInterface` is the contract mirror for service-layer behavior. It is not an implementation layer.

Rules enforced by the audit tool:

1. PHP symbols in `src/ServiceInterface` must be interfaces.
2. Interface names must end with `Interface`.
3. Service-interface namespaces must stay under `App\ServiceInterface`.
4. Every ServiceInterface capability directory should have a `MANIFEST.md` boundary note.

## Why this wave is separate

The previous extraction wave moved contracts out of `src/Service`. This wave makes the extracted layer self-documenting and auditable before larger service-tree movement begins.

## Expected audit posture

After this wave and after Wave 8 residual cleanup has been applied, the important counters should be:

```text
service interface directories missing manifest: 0
wave8 legacy service interface files: 0
wave8 legacy event subscriber files: 0
```

If the Wave 8 counters remain non-zero, re-run the Wave 8 touched-file apply script before treating the tree as canon-clean for those paths.

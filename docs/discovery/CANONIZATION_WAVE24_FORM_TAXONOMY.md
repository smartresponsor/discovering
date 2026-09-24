# Discovering Canonization Wave 24 — Form Taxonomy Hardening

Wave 24 documents and machine-checks the Symfony form layer after Wave 5 form-boundary hardening.

## Scope

Touched files:

- `src/Form/MANIFEST.md`
- `src/Form/Discovery/MANIFEST.md`
- `docs/discovery/CANONIZATION_WAVE24_FORM_TAXONOMY.md`
- `tools/discovering_canon_audit.php`

## Facts from the current slice

`src/Form/Discovery` currently contains one Symfony form type:

- `DiscoverySearchType`

The form is already named correctly and keeps query/search child fields unmapped so the readonly `DiscoveryQueryDTO` DTO is not mutated by Symfony Form submit handling.

## Canonical posture

Forms are Symfony input/presentation adapters. They should not become business services and should not own immutable DTO construction when the controller/request boundary already owns it.

## Non-goals

- No form rewrite.
- No field changes.
- No controller changes.
- No DTO changes.
- No runtime behavior changes.

## Verification

Run:

```bash
php tools/discovering_canon_audit.php
```

Expected form taxonomy counter:

```text
wave24 form taxonomy findings: 0
```

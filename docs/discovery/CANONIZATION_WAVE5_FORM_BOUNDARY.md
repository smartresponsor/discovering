# Discovering Canonization Wave 5 — Form Boundary Hardening

Wave 5 keeps the existing Symfony form class name because `DiscoverySearchType` already follows the `*Type` suffix convention. The cleanup target is the form boundary itself.

## Finding

`DiscoverySearchType` renders a search/query surface for the public discovery UI, while `DiscoveryController` already builds the authoritative `DiscoveryQueryDTO` from the HTTP request. After Wave 4, `DiscoveryQueryDTO` is an immutable `readonly` DTO. A mapped Symfony form over that DTO is therefore the wrong boundary: form submission can try to write into an immutable data carrier.

## Change

- Keep `App\Form\Discovery\DiscoverySearchType` in the canonical Form layer.
- Keep the `*Type` suffix.
- Mark all child fields as `mapped: false`.
- Preserve initial field values from the provided `DiscoveryQueryDTO` for rendering.
- Preserve `DiscoveryQueryDTO::class` as the root data class so callers still pass the same canonical DTO.
- Add a unit test proving form submission does not mutate the immutable query DTO.

## Non-goals

- No Entity changes.
- No Doctrine migration changes.
- No controller route changes.
- No namespace policy change.
- No cumulative snapshot or destructive repository overwrite.

## Validation

```bash
php -l src/Form/Discovery/DiscoverySearchType.php
php -l tests/Unit/Discovery/DiscoverySearchTypeTest.php
vendor/bin/phpunit tests/Unit/Discovery/DiscoverySearchTypeTest.php
php bin/console lint:container
```

# Discovering Canonization Wave 4 — DTO / ValueObject Shape Cleanup

## Scope

This wave normalizes the immutable data-carrier surface for the Discovering component. It is intentionally narrow:

- no Entity relocation;
- no Doctrine mapping change;
- no database migration change;
- no repository-wide cleanup;
- no cumulative snapshot overwrite;
- no namespace policy decision beyond the existing `App\...` namespace in this current slice.

## Canonical decision

DTO and ValueObject classes that are constructed from promoted public properties and have no observed post-construction mutation are now declared as `readonly` classes.

This preserves the current public property access style while making the intended runtime contract explicit:

- DTOs are transport/application result contracts;
- ValueObjects are immutable discovery domain values;
- Entities remain mutable Doctrine persistence models and are intentionally excluded from this wave.

## Touched shape groups

- `src/Dto/Discovery/*`
- `src/ValueObject/Discovery/*`

The directory-backed family base DTOs remain non-final because `Briefing*` and `Playbook*` DTOs extend them. They are now `readonly class` bases and their concrete children are `final readonly class` leaves.

## Why this wave is safe

The current source scan did not find DTO/ValueObject property writes outside constructors. Existing read access such as `$query->resource`, `$result->hits`, or `$document->title` remains valid.

The wave does not replace DTOs with private getters and does not introduce serializer-specific attributes. It only hardens the existing public-promoted-property contract.

## Follow-up candidates

1. Introduce a separate `src/Request/Discovery` or `src/FormModel/Discovery` layer only where Symfony forms/controllers need mutable input models.
2. Split true result DTOs from command/input DTOs after controller and form cleanup.
3. Add a DTO/value-object guard to CI once the component namespace and root layout are fully stabilized.
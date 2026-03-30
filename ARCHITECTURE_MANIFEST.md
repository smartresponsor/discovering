# Architecture Manifest

## Global architecture frame
- Symfony-oriented application
- single `App\\` namespace
- single production root `src/`
- no `src/Domain/`
- no Port-and-Adapter wording or structure
- keep responsibility explicit and path-aligned

## Layering direction
- Controllers stay thin
- Services express business behavior
- ServiceInterface mirrors Service where useful
- DTOs model input/output flow shape
- ValueObjects strengthen semantics
- Commands cover operational routines
- Forms and Twig pages should support demonstrable flows

## Evolution rule
Build a coherent product component, not a pile of abstractions. Keep naming centered on Discovery and application resource discovery.

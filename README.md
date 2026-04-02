# Discovering

Discovering is a Symfony-oriented application resource discovery component.

Workspace name: **Discovering**  
Core entity name: **Discovery**

The repository is intended to evolve as a Laravel-Scout-like discovery layer for Symfony applications. Its mission is not to become a generic search engine, but to help applications expose, index, scope, and retrieve relevant resources through a coherent discovery model.

## Current business seed
- seeded discovery source providers for demo and development
- typed query / result model
- discovery service and indexer
- operator overview and source diagnostics
- local demo adapter for resource discovery flows
- Twig search UI with status and visibility filters
- JSON API for discovery queries
- libsource event log viewer with search, level filters, quick presets, and pagination
- file-backed live source sample for playbook discovery records
- playbook source import / export tooling for JSON-backed records
- playbook management surface for reviewing live file-backed records and export flows
- multi-file live source registry for playbook records with directory-backed aggregation and legacy fallback
- inline playbook management actions for registry audit, sample seeding, and legacy migration
- playbook operator event trail and CLI history output for management observability
- second live source family for briefing discovery records with its own directory-backed registry and management surface
- shared file-backed source family foundation for reusable directory/legacy/import/export repository behavior
- shared family management surface foundation for reusable directory-backed live family UI aggregation
- shared operator-action and operator-event foundation for reusable live-family action, resolver, and trail behavior
- shared family controller and Twig foundation for reusable live-family management rendering and export helpers
- discovery engine ranking with resource weights, filter-aware scoring, and match reasons
- explainable discovery UI with score, ranking reasons, and engine-aware query controls
- hybrid ranking that combines SQLite FTS bm25 relevance with explainable custom scoring
- query modes and preset weighting strategies for relevance, governance, operations, and exploration discovery intents
- content snippets with safe token highlighting for titles, references, and contextual excerpts in UI and API

## Current routes
- `/discovery` — human-facing discovery UI
- `/api/discovery` — machine-facing JSON discovery endpoint
- `/management/discovery` — operator overview

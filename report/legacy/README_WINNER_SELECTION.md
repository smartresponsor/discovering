# Search winner-repo selection

This repository slice was assembled from two uploaded archives by keeping only files that are directly relevant to the application-search idea and are at least syntactically viable.

## Included winners
- `src/SearchInterface/Adapter/SearchAdapterInterface.php`
- `src/SearchAdapter/SqliteFtsAdapter.php`
- `src/SearchAdapter/MeiliAdapter.php`
- `src/Controller/Search/SearchController.php`
- `config/services_search.yaml`

## Excluded from winner repo
### Broken / invalid
- `src/SearchAdapter/ElasticAdapter.php`
  - invalid PHP syntax
  - contains pseudocode / non-PHP fragments

### Not part of the search component core
- entire ops bundle from `Search.zip`
- payment provider files
- helm charts
- github workflows
- connect scripts
- k6 profile
- non-search overlays / readmes
- ClamAV feature slice
- Stripe E2E slice

## Current stage of this winner repo
This is still a sketch-level repository slice, not a production-ready component.
It contains the most salvageable search-related files only.

## Notes
- `SearchController` is hardcoded to the `project` index.
- `services_search.yaml` binds the interface to `SqliteFtsAdapter` by default.
- `SqliteFtsAdapter` and `MeiliAdapter` are the only currently viable adapters from the uploaded material.

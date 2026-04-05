# Shared state topology v1

The topology export now makes mutable discovery state inspectable instead of implicit.

## Stores currently described

- discovery index (`sqlite` or shared `meilisearch` service mode)
- feedback store (`sqlite_path` or `pdo`)
- operation log
- rebuild evidence
- libsource event log
- rate-limit store

## Stronger coordination seams

The following stores can now be configured as stronger shared coordination stores instead of remaining local-only:

- discovery index (`sqlite` or shared `meilisearch` service mode)
- feedback store (`sqlite_path` or `pdo`)
- operation log
- rebuild evidence
- libsource event log
- rate-limit store

Depending on the chosen backend, descriptors can now move from local file-oriented storage toward either:

- `backend: pdo_table` with `storageMode: database`
- `backend: meilisearch` with `storageMode: service`

## Why distributedReady can still remain false

Even when these stores use shared PDO backends, overall discovery state still includes at least:

- SQLite-backed search index state

If feedback learning also remains on the default SQLite path, it stays single-node too. When the discovery index is switched to shared Meilisearch service mode and the remaining mutable state uses stronger coordination backends, the topology can now honestly report `distributedReady: true`.

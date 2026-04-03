# Shared state topology v1

The topology export now makes mutable discovery state inspectable instead of implicit.

## Stores currently described

- discovery index
- feedback store
- operation log
- rebuild evidence
- libsource event log
- rate-limit store

## Stronger coordination seams

The following stores can now be configured as PDO-backed coordination tables instead of local JSON/SQLite files:

- feedback store
- operation log
- rebuild evidence
- libsource event log
- rate-limit store

This changes each descriptor from:

- `backend: json_file`
- `storageMode: local_file|shared_file`

into:

- `backend: pdo_table`
- `storageMode: database`

## Why distributedReady can still remain false

Even when these stores use shared PDO backends, overall discovery state still includes at least:

- SQLite-backed search index state

If feedback learning also remains on the default SQLite path, it stays single-node too. So the topology can now show **multiple stronger coordination stores** without falsely claiming that the whole component is multi-replica write-ready.

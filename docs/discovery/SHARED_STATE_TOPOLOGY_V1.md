# Shared state topology v1

The topology export now makes mutable discovery state inspectable instead of implicit.

## Stores currently described

- discovery index
- feedback store
- operation log
- rebuild evidence
- libsource event log
- rate-limit store

## New stronger coordination seam

The rate-limit store can now be configured as a PDO-backed coordination table instead of a JSON file.

This changes the rate-limit descriptor from:

- `backend: json_file`
- `storageMode: local_file|shared_file`

into:

- `backend: pdo_table`
- `storageMode: database`

## Why distributedReady can still remain false

Even when rate limiting uses a shared PDO backend, overall discovery state still includes:

- SQLite-backed search index state
- SQLite-backed feedback state
- JSON-file-backed operation and evidence logs
- JSON-file-backed libsource operator event logs

So the topology can now show **one stronger coordination store** without falsely claiming that the whole component is multi-replica write-ready.

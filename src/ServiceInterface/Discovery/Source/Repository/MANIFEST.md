# Discovery Source Repository Interface Manifest

Source repository contracts define collection access for discovery source records without binding consumers to a storage backend.

Rules:

- Repository contracts must end with `RepositoryInterface`.
- Implementations remain under the service layer unless a Doctrine repository is introduced later as a persistence-specific collaborator.

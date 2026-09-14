# Unit Discovery Test Manifest

Use this bucket for focused tests of DTOs, value objects, pure services, small policies, scoring logic, stores, codecs and repository/backend collaborators.

Unit tests should avoid booting the Symfony kernel unless the class under test explicitly requires a framework boundary. Framework, HTTP, route, form and container checks belong in `tests/Functional/Discovery/`.

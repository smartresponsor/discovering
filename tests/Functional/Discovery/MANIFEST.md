# Functional Discovery Test Manifest

Use this bucket for Symfony kernel, HTTP, controller, route, form, security-header, rate-limit and container-facing flows.

Functional tests may use shared helpers from `tests/Support/` and the local `AbstractDiscoveryWebTestCase`, but assertions about public API envelopes that must remain stable for consumers should be promoted to `tests/Contract/Discovery/`.

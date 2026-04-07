<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$checks = [
    'security_headers_subscriber' => $projectRoot . '/src/EventSubscriber/DiscoveryResponseSecurityHeadersSubscriber.php',
    'security_endpoint_subscriber' => $projectRoot . '/src/EventSubscriber/DiscoveryEndpointSecuritySubscriber.php',
    'rate_limit_subscriber' => $projectRoot . '/src/EventSubscriber/DiscoveryRateLimitSubscriber.php',
    'request_correlation_subscriber' => $projectRoot . '/src/EventSubscriber/DiscoveryRequestCorrelationSubscriber.php',
    'openapi_config' => $projectRoot . '/config/packages/nelmio_api_doc.php',
    'openapi_routes' => $projectRoot . '/config/routes/nelmio_api_doc.php',
    'rc_readiness_doc' => $projectRoot . '/docs/discovery/RC_READINESS.md',
    'support_matrix_doc' => $projectRoot . '/docs/discovery/SUPPORT_MATRIX.md',
    'known_limitations_doc' => $projectRoot . '/docs/discovery/KNOWN_LIMITATIONS.md',
];

$failures = [];
foreach ($checks as $name => $path) {
    if (!is_file($path)) {
        $failures[] = sprintf('%s: missing %s', $name, substr($path, strlen($projectRoot) + 1));
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Security preflight failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

fwrite(STDOUT, "Security preflight passed.\n");

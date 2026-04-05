<?php

declare(strict_types=1);

$errors = [];
$servicesYaml = file_get_contents(__DIR__ . '/../config/services.yaml');
if (!is_string($servicesYaml)) {
    $errors[] = 'Unable to read config/services.yaml.';
} else {
    foreach (['APP_DISCOVERY_MANAGEMENT_TOKEN', 'APP_DISCOVERY_API_WRITE_TOKEN'] as $requiredEnv) {
        if (!str_contains($servicesYaml, $requiredEnv)) {
            $errors[] = sprintf('Expected services.yaml to reference %s.', $requiredEnv);
        }
    }
}

foreach ([
    __DIR__ . '/../docs/discovery/SECURITY_BASELINE.md',
    __DIR__ . '/../docs/discovery/OPENAPI_BASELINE.md',
    __DIR__ . '/../phpstan.neon.dist',
    __DIR__ . '/../.php-cs-fixer.dist.php',
] as $path) {
    if (!is_file($path)) {
        $errors[] = sprintf('Missing required hardening artifact: %s', $path);
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Security preflight failed:
- " . implode("
- ", $errors) . "
");
    exit(1);
}

fwrite(STDOUT, "Security preflight passed.
");

<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$targets = [
    'src/Service/Discovery/RateLimit/FileDiscoveryRateLimitStore.php',
    'src/Service/Discovery/RateLimit/PdoDiscoveryRateLimitStore.php',
    'src/Service/Discovery/PdoDiscoveryFeedbackStore.php',
    'src/Service/Discovery/Operations/PdoDiscoveryOperationEventLogStore.php',
];

$missing = [];
foreach ($targets as $target) {
    $path = $projectRoot . '/' . $target;
    if (!is_file($path)) {
        $missing[] = sprintf('%s (file missing)', $target);
        continue;
    }

    $contents = file_get_contents($path);
    if (!is_string($contents) || !str_contains($contents, '/**')) {
        $missing[] = sprintf('%s (docblock missing)', $target);
    }
}

if ($missing !== []) {
    fwrite(STDERR, "Docblock policy check failed:\n- " . implode("\n- ", $missing) . "\n");
    exit(1);
}

fwrite(STDOUT, "Docblock policy check passed.\n");

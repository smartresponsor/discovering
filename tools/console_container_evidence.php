<?php

declare(strict_types=1);

/**
 * Generates console/container evidence for Discovering.
 *
 * This proof is intentionally separate from structural closure and dependency
 * evidence because it requires installed vendor dependencies and a bootable
 * Symfony kernel/container.
 */

$projectRoot = dirname(__DIR__);
$outputPath = $projectRoot . '/var/discovery/evidence/console_container_evidence.json';

foreach ($argv as $index => $arg) {
    if ($arg === '--output' && isset($argv[$index + 1])) {
        $outputPath = $argv[$index + 1];
    }
}

$commands = [
    'runtime_vendor_preflight' => [PHP_BINARY, $projectRoot . '/tools/runtime_preflight.php', '--require-vendor', '--json'],
    'console_list_raw' => [PHP_BINARY, $projectRoot . '/bin/console', 'list', '--raw'],
    'cache_clear_dev' => [PHP_BINARY, $projectRoot . '/bin/console', 'cache:clear', '--env=dev', '--no-warmup'],
    'container_lint_dev' => [PHP_BINARY, $projectRoot . '/bin/console', 'lint:container', '--env=dev'],
];

$results = [];
$overallOk = true;

foreach ($commands as $nameEntity => $command) {
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $projectRoot);
    if (!is_resource($process)) {
        $results[$nameEntity] = [
            'ok' => false,
            'exitCode' => 127,
            'stdout' => '',
            'stderr' => 'Failed to start process.',
            'json' => null,
        ];
        $overallOk = false;
        continue;
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    $decoded = null;

    if ($stdout !== false && trim($stdout) !== '') {
        $jsonCandidate = json_decode($stdout, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $decoded = $jsonCandidate;
        }
    }

    $ok = $exitCode === 0;
    if (!$ok) {
        $overallOk = false;
    }

    $results[$nameEntity] = [
        'ok' => $ok,
        'exitCode' => $exitCode,
        'stdout' => $stdout === false ? '' : $stdout,
        'stderr' => $stderr === false ? '' : $stderr,
        'json' => $decoded,
    ];
}

$evidence = [
    'component' => 'Discovering',
    'generatedAt' => gmdate('c'),
    'projectRoot' => $projectRoot,
    'ok' => $overallOk,
    'boundary' => 'console_container',
    'checks' => $results,
];

$outputDirectory = dirname($outputPath);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf("Cannot create evidence directory: %s\n", $outputDirectory));
    exit(1);
}

file_put_contents(
    $outputPath,
    json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

fwrite(STDOUT, sprintf("Console/container evidence written: %s\n", $outputPath));
fwrite(STDOUT, sprintf("Result: %s\n", $overallOk ? 'PASS' : 'FAIL'));

exit($overallOk ? 0 : 1);

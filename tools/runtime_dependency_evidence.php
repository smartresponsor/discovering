<?php

declare(strict_types=1);

/**
 * Generates runtime dependency evidence for Discovering.
 *
 * This is intentionally separate from structural closure evidence. It verifies
 * machine/environment readiness gates such as Composer, vendor autoload and
 * PHP runtime/test extensions.
 */

$projectRoot = dirname(__DIR__);
$outputPath = $projectRoot . '/var/discovery/evidence/runtime_dependency_evidence.json';

foreach ($argv as $index => $arg) {
    if ($arg === '--output' && isset($argv[$index + 1])) {
        $outputPath = $argv[$index + 1];
    }
}

$commands = [
    'runtime_extensions' => [PHP_BINARY, $projectRoot . '/tools/runtime_preflight.php', '--check-runtime-extensions', '--json'],
    'composer_runtime' => [PHP_BINARY, $projectRoot . '/tools/runtime_preflight.php', '--require-composer', '--json'],
    'vendor_runtime' => [PHP_BINARY, $projectRoot . '/tools/runtime_preflight.php', '--require-vendor', '--json'],
    'test_runtime' => [PHP_BINARY, $projectRoot . '/tools/runtime_preflight.php', '--test-runtime', '--json'],
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
    'boundary' => 'runtime_dependency',
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

fwrite(STDOUT, sprintf("Runtime dependency evidence written: %s\n", $outputPath));
fwrite(STDOUT, sprintf("Result: %s\n", $overallOk ? 'PASS' : 'FAIL'));

exit($overallOk ? 0 : 1);

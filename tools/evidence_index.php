<?php

declare(strict_types=1);

/**
 * Builds a Discovering evidence index from generated evidence JSON files.
 *
 * This tool is read-only except for writing the index JSON. It does not require
 * Composer autoload or vendor dependencies.
 */

$projectRoot = dirname(__DIR__);
$evidenceDirectory = $projectRoot . '/var/discovery/evidence';
$outputPath = $evidenceDirectory . '/evidence_index.json';

foreach ($argv as $index => $arg) {
    if ($arg === '--output' && isset($argv[$index + 1])) {
        $outputPath = $argv[$index + 1];
    }
}

$expectedEvidenceFiles = [
    'structural_closure' => $evidenceDirectory . '/structural_closure_evidence.json',
    'runtime_dependency' => $evidenceDirectory . '/runtime_dependency_evidence.json',
    'console_container' => $evidenceDirectory . '/console_container_evidence.json',
];

$entries = [];
$overallOk = true;

foreach ($expectedEvidenceFiles as $nameEntity => $path) {
    if (!is_file($path)) {
        $entries[$nameEntity] = [
            'present' => false,
            'ok' => false,
            'path' => $path,
            'generatedAt' => null,
            'boundary' => $nameEntity,
            'summary' => 'Evidence file is missing.',
        ];
        $overallOk = false;
        continue;
    }

    $contents = (string) file_get_contents($path);
    $decoded = json_decode($contents, true);

    if (!is_array($decoded)) {
        $entries[$nameEntity] = [
            'present' => true,
            'ok' => false,
            'path' => $path,
            'generatedAt' => null,
            'boundary' => $nameEntity,
            'summary' => 'Evidence file is not valid JSON.',
        ];
        $overallOk = false;
        continue;
    }

    $ok = (bool) ($decoded['ok'] ?? false);
    if (!$ok) {
        $overallOk = false;
    }

    $entries[$nameEntity] = [
        'present' => true,
        'ok' => $ok,
        'path' => $path,
        'generatedAt' => $decoded['generatedAt'] ?? null,
        'boundary' => $decoded['boundary'] ?? $nameEntity,
        'summary' => $ok ? 'PASS' : 'FAIL',
    ];
}

$index = [
    'component' => 'Discovering',
    'generatedAt' => gmdate('c'),
    'projectRoot' => $projectRoot,
    'ok' => $overallOk,
    'evidenceDirectory' => $evidenceDirectory,
    'entries' => $entries,
];

$outputDirectory = dirname($outputPath);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf("Cannot create evidence index directory: %s\n", $outputDirectory));
    exit(1);
}

file_put_contents(
    $outputPath,
    json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

fwrite(STDOUT, sprintf("Evidence index written: %s\n", $outputPath));
foreach ($entries as $nameEntity => $entry) {
    fwrite(STDOUT, sprintf(
        "- %s: %s%s\n",
        $nameEntity,
        $entry['summary'],
        $entry['present'] ? '' : ' (missing)'
    ));
}
fwrite(STDOUT, sprintf("Result: %s\n", $overallOk ? 'PASS' : 'FAIL'));

exit($overallOk ? 0 : 1);

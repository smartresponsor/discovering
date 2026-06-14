<?php

declare(strict_types=1);

/**
 * Builds a human-readable Markdown summary from Discovering evidence JSON.
 *
 * This tool reads `var/discovery/evidence/evidence_index.json` by default and
 * writes `var/discovery/evidence/evidence_summary.md`.
 */

$projectRoot = dirname(__DIR__);
$inputPath = $projectRoot . '/var/discovery/evidence/evidence_index.json';
$outputPath = $projectRoot . '/var/discovery/evidence/evidence_summary.md';

foreach ($argv as $index => $arg) {
    if ($arg === '--input' && isset($argv[$index + 1])) {
        $inputPath = $argv[$index + 1];
    }

    if ($arg === '--output' && isset($argv[$index + 1])) {
        $outputPath = $argv[$index + 1];
    }
}

if (!is_file($inputPath)) {
    fwrite(STDERR, sprintf("Evidence index not found: %s\n", $inputPath));
    exit(1);
}

$index = json_decode((string) file_get_contents($inputPath), true);
if (!is_array($index)) {
    fwrite(STDERR, sprintf("Evidence index is not valid JSON: %s\n", $inputPath));
    exit(1);
}

$entries = is_array($index['entries'] ?? null) ? $index['entries'] : [];
$lines = [
    '# Discovering Evidence Summary',
    '',
    sprintf('- Generated at: `%s`', gmdate('c')),
    sprintf('- Source index: `%s`', $inputPath),
    sprintf('- Overall result: **%s**', (bool) ($index['ok'] ?? false) ? 'PASS' : 'FAIL'),
    '',
    '## Boundaries',
    '',
    '| Boundary | Present | Result | Generated at |',
    '| --- | --- | --- | --- |',
];

foreach ($entries as $nameEntity => $entry) {
    $lines[] = sprintf(
        '| `%s` | %s | **%s** | `%s` |',
        (string) $nameEntity,
        (bool) ($entry['present'] ?? false) ? 'yes' : 'no',
        (string) ($entry['summary'] ?? ((bool) ($entry['ok'] ?? false) ? 'PASS' : 'FAIL')),
        (string) ($entry['generatedAt'] ?? 'n/a')
    );
}

$lines[] = '';
$lines[] = '## Interpretation';
$lines[] = '';

if ((bool) ($index['ok'] ?? false)) {
    $lines[] = 'All evidence boundaries currently report **PASS**.';
} else {
    $lines[] = 'One or more strict evidence boundaries currently report **FAIL** or are missing.';
    $lines[] = '';
    $lines[] = 'This does not invalidate structural closure when `structural_closure` is passing. Runtime dependency and console/container proof require a provisioned machine.';
}

$lines[] = '';

$outputDirectory = dirname($outputPath);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf("Cannot create evidence summary directory: %s\n", $outputDirectory));
    exit(1);
}

file_put_contents($outputPath, implode("\n", $lines) . "\n");

fwrite(STDOUT, sprintf("Evidence summary written: %s\n", $outputPath));
fwrite(STDOUT, sprintf("Result: %s\n", (bool) ($index['ok'] ?? false) ? 'PASS' : 'FAIL'));

exit((bool) ($index['ok'] ?? false) ? 0 : 1);

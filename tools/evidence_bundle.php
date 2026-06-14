<?php

declare(strict_types=1);

/**
 * Exports Discovering evidence artifacts into a ZIP bundle.
 *
 * The tool is repository-local and does not require Composer autoload or the
 * PHP ZipArchive extension. ZIP entries are written with the STORE method.
 */

$projectRoot = dirname(__DIR__);
$evidenceDirectory = $projectRoot . '/var/discovery/evidence';
$outputPath = $evidenceDirectory . '/discovering_evidence_bundle.zip';

foreach ($argv as $index => $arg) {
    if ($arg === '--output' && isset($argv[$index + 1])) {
        $outputPath = $argv[$index + 1];
    }
}

$artifactFiles = [
    'structural_closure_evidence.json',
    'runtime_dependency_evidence.json',
    'console_container_evidence.json',
    'evidence_index.json',
    'evidence_summary.md',
];

if (!is_dir($evidenceDirectory) && !mkdir($evidenceDirectory, 0775, true) && !is_dir($evidenceDirectory)) {
    fwrite(STDERR, sprintf("Cannot create evidence directory: %s\n", $evidenceDirectory));
    exit(1);
}

$outputDirectory = dirname($outputPath);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf("Cannot create bundle output directory: %s\n", $outputDirectory));
    exit(1);
}

$manifest = [
    'component' => 'Discovering',
    'generatedAt' => gmdate('c'),
    'projectRoot' => $projectRoot,
    'evidenceDirectory' => $evidenceDirectory,
    'files' => [],
];

$entries = [];
foreach ($artifactFiles as $artifactFile) {
    $path = $evidenceDirectory . '/' . $artifactFile;
    $exists = is_file($path);

    $manifest['files'][] = [
        'nameEntity' => $artifactFile,
        'present' => $exists,
        'bytes' => $exists ? filesize($path) : 0,
    ];

    if ($exists) {
        $entries[$artifactFile] = (string) file_get_contents($path);
    }
}

$entries['bundle_manifest.json'] = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

/**
 * @param array<string, string> $entries
 */
function writeStoreZip(string $outputPath, array $entries): void
{
    $handle = fopen($outputPath, 'wb');
    if ($handle === false) {
        throw new RuntimeException(sprintf('Cannot open evidence bundle for writing: %s', $outputPath));
    }

    $centralDirectory = '';
    $offset = 0;

    foreach ($entries as $nameEntity => $contents) {
        $nameEntity = str_replace('\\', '/', $nameEntity);
        $nameLength = strlen($nameEntity);
        $size = strlen($contents);
        $crc = crc32($contents);
        if ($crc < 0) {
            $crc += 4294967296;
        }

        $localHeader = pack(
            'VvvvvvVVVvv',
            0x04034b50,
            20,
            0,
            0,
            0,
            0,
            $crc,
            $size,
            $size,
            $nameLength,
            0
        ) . $nameEntity;

        fwrite($handle, $localHeader);
        fwrite($handle, $contents);

        $centralDirectory .= pack(
            'VvvvvvvVVVvvvvvVV',
            0x02014b50,
            20,
            20,
            0,
            0,
            0,
            0,
            $crc,
            $size,
            $size,
            $nameLength,
            0,
            0,
            0,
            0,
            0,
            $offset
        ) . $nameEntity;

        $offset += strlen($localHeader) + $size;
    }

    $centralDirectorySize = strlen($centralDirectory);
    fwrite($handle, $centralDirectory);

    $endOfCentralDirectory = pack(
        'VvvvvVVv',
        0x06054b50,
        0,
        0,
        count($entries),
        count($entries),
        $centralDirectorySize,
        $offset,
        0
    );

    fwrite($handle, $endOfCentralDirectory);
    fclose($handle);
}

try {
    writeStoreZip($outputPath, $entries);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, sprintf("Evidence bundle written: %s\n", $outputPath));
foreach ($manifest['files'] as $file) {
    fwrite(STDOUT, sprintf(
        "- %s: %s\n",
        $file['nameEntity'],
        $file['present'] ? sprintf('included (%d bytes)', $file['bytes']) : 'missing'
    ));
}
fwrite(STDOUT, "- bundle_manifest.json: included\n");

exit(0);

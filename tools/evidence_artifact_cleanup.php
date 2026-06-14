<?php

declare(strict_types=1);

/**
 * Removes generated Discovering evidence artifacts from var/discovery/evidence.
 *
 * This tool is intentionally scoped to known generated files only. It does not
 * delete arbitrary directories or recursively clear the repository.
 */

$projectRoot = dirname(__DIR__);
$evidenceDirectory = $projectRoot . '/var/discovery/evidence';
$dryRun = in_array('--dry-run', $argv, true);

$generatedFiles = [
    'structural_closure_evidence.json',
    'runtime_dependency_evidence.json',
    'console_container_evidence.json',
    'evidence_index.json',
    'evidence_summary.md',
    'discovering_evidence_bundle.zip',
];

fwrite(STDOUT, sprintf("Evidence artifact cleanup%s\n", $dryRun ? ' (dry-run)' : ''));
fwrite(STDOUT, sprintf("Directory: %s\n", $evidenceDirectory));

foreach ($generatedFiles as $generatedFile) {
    $path = $evidenceDirectory . '/' . $generatedFile;

    if (!is_file($path)) {
        fwrite(STDOUT, sprintf("- absent: %s\n", $generatedFile));
        continue;
    }

    if ($dryRun) {
        fwrite(STDOUT, sprintf("- would remove: %s\n", $generatedFile));
        continue;
    }

    unlink($path);
    fwrite(STDOUT, sprintf("- removed: %s\n", $generatedFile));
}

fwrite(STDOUT, "Evidence artifact cleanup completed.\n");
exit(0);

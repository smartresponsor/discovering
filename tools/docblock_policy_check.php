<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$targets = ['src', 'tests'];
$classPattern = '/^(?:final\s+|abstract\s+)?(?:readonly\s+)?(?:class|interface|trait|enum)\s+[A-Za-z_][A-Za-z0-9_]*/m';
$missing = [];

foreach ($targets as $targetRoot) {
    $directory = $projectRoot . '/' . $targetRoot;
    if (!is_dir($directory)) {
        $missing[] = sprintf('%s (directory missing)', $targetRoot);
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if (!is_string($contents) || !preg_match($classPattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        $classOffset = $matches[0][1];
        $prefix = substr($contents, 0, $classOffset);
        if (!is_string($prefix)) {
            continue;
        }

        $tail = implode("\n", array_slice(preg_split('/\R/', $prefix) ?: [], -8));
        if (!str_contains($tail, '/**')) {
            $missing[] = str_replace($projectRoot . '/', '', $file->getPathname());
        }
    }
}

if ($missing !== []) {
    fwrite(STDERR, "Docblock policy check failed. Missing class-level semantic docblocks in:\n- " . implode("\n- ", $missing) . "\n");
    exit(1);
}

fwrite(STDOUT, "Docblock policy check passed for src/ and tests/.\n");

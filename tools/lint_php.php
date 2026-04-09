<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$paths = [
    $projectRoot . '/src',
    $projectRoot . '/tests',
];

$files = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $path = $fileInfo->getPathname();
    if (str_ends_with($path, '.php') && (str_starts_with($path, $paths[0]) || str_starts_with($path, $paths[1]))) {
        $files[] = $path;
    }
}

sort($files);

$failures = 0;
foreach ($files as $file) {
    $command = sprintf('php -l %s', escapeshellarg($file));
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);
    if ($exitCode !== 0) {
        ++$failures;
        fwrite(STDOUT, implode(PHP_EOL, $output) . PHP_EOL);
    }
}

if ($failures === 0) {
    fwrite(STDOUT, sprintf("PHP lint passed for %d files.%s", count($files), PHP_EOL));
    exit(0);
}

fwrite(STDERR, sprintf("PHP lint failed for %d file(s).%s", $failures, PHP_EOL));
exit(1);

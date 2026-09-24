<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directory = $root.'/var/schema-parity';
$database = $directory.'/discovering.sqlite';

if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
    throw new RuntimeException('Unable to create schema parity directory.');
}

foreach ([$database, $database.'-shm', $database.'-wal'] as $path) {
    if (is_file($path) && !unlink($path)) {
        throw new RuntimeException(sprintf('Unable to reset schema parity artifact "%s".', $path));
    }
}

$run = static function (array $command) use ($root): void {
    $process = proc_open($command, [1 => STDOUT, 2 => STDERR], $pipes, $root);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start Doctrine schema parity command.');
    }

    $exit = proc_close($process);
    if (0 !== $exit) {
        throw new RuntimeException(sprintf('Doctrine schema parity command failed with exit code %d.', $exit));
    }
};

try {
    $common = ['--em=infra', '--env=schema_parity', '--no-interaction'];
    $run(array_merge([PHP_BINARY, 'bin/console', 'doctrine:migrations:migrate'], $common));
    $run([PHP_BINARY, 'bin/console', 'doctrine:schema:validate', '--em=infra', '--env=schema_parity', '--no-interaction']);
    $run([PHP_BINARY, 'bin/console', 'doctrine:migrations:up-to-date', '--em=infra', '--env=schema_parity', '--no-interaction']);
    fwrite(STDOUT, "Doctrine schema parity passed on isolated SQLite.\n");
} finally {
    foreach ([$database, $database.'-shm', $database.'-wal'] as $path) {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}


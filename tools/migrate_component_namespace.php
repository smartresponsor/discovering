<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directories = ['src', 'tests', 'bin', 'public', 'config'];
$prefixes = [
    'App\\Command\\',
    'App\\Controller\\',
    'App\\Dto\\',
    'App\\Entity\\',
    'App\\Form\\',
    'App\\Repository\\',
    'App\\RepositoryInterface\\',
    'App\\Service\\',
    'App\\ServiceInterface\\',
    'App\\Subscriber\\',
    'App\\Tests\\',
    'App\\ValueObject\\',
    'App\\Kernel',
];

$extensions = ['php', 'yaml', 'yml', 'xml'];
$changed = 0;

foreach ($directories as $directory) {
    $path = $root . DIRECTORY_SEPARATOR . $directory;
    if (!is_dir($path) && !is_file($path)) {
        continue;
    }

    $files = is_file($path)
        ? [new SplFileInfo($path)]
        : new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));

    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, $extensions, true) && !in_array($file->getFilename(), ['console'], true)) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new RuntimeException('Unable to read ' . $file->getPathname());
        }

        $updated = $contents;
        foreach ($prefixes as $prefix) {
            $updated = str_replace($prefix, 'App\\Discovering\\' . substr($prefix, 4), $updated);
        }

        if ($updated !== $contents) {
            file_put_contents($file->getPathname(), $updated);
            ++$changed;
        }
    }
}

fwrite(STDOUT, sprintf("Updated %d files.\n", $changed));

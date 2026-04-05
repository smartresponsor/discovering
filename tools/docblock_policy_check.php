<?php

declare(strict_types=1);

$roots = [__DIR__ . '/../src'];
$checkedFiles = 0;
$descriptiveBlocks = 0;

foreach ($roots as $root) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        ++$checkedFiles;
        $content = file_get_contents($file->getPathname());
        if (!is_string($content)) {
            continue;
        }

        preg_match_all('/\/\*\*(.*?)\*\//s', $content, $matches);
        foreach ($matches[1] as $block) {
            $lines = preg_split('/\R/', $block) ?: [];
            foreach ($lines as $line) {
                $normalized = trim(ltrim(trim($line), '*'));
                if ($normalized === '' || str_starts_with($normalized, '@')) {
                    continue;
                }

                ++$descriptiveBlocks;
                break;
            }
        }
    }
}

if ($descriptiveBlocks === 0) {
    fwrite(STDERR, "Docblock policy check failed: no descriptive documentation blocks were detected.\n");
    exit(1);
}

fwrite(STDOUT, sprintf(
    "Docblock policy check passed for %d files with %d descriptive documentation block(s).\n",
    $checkedFiles,
    $descriptiveBlocks,
));

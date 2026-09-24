<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$paths = [
    realpath($projectRoot . '/src'),
    realpath($projectRoot . '/tests'),
];

$files = [];

$iterator = new AppendIterator();
foreach ($paths as $root) {
    if (false === $root) {
        continue;
    }

    $iterator->append(new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    ));
}

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $path = $fileInfo->getPathname();
    if (str_ends_with($path, '.php')) {
        $files[] = $path;
    }
}

sort($files);

$failures = 0;
$nextFile = 0;
$running = [];
$concurrency = max(2, min(8, count($files)));

while ($nextFile < count($files) || $running !== []) {
    while ($nextFile < count($files) && count($running) < $concurrency) {
        $file = $files[$nextFile++];
        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, '-l', $file],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            ++$failures;
            fwrite(STDERR, sprintf('Unable to start PHP lint for %s.%s', $file, PHP_EOL));
            continue;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $running[] = [
            'file' => $file,
            'process' => $process,
            'stdout' => $pipes[1],
            'stderr' => $pipes[2],
        ];
    }

    foreach ($running as $key => $job) {
        $status = proc_get_status($job['process']);
        if ($status['running']) {
            continue;
        }

        $stdout = stream_get_contents($job['stdout']);
        $stderr = stream_get_contents($job['stderr']);
        fclose($job['stdout']);
        fclose($job['stderr']);

        $exitCode = $status['exitcode'];
        proc_close($job['process']);

        if (0 !== $exitCode) {
            ++$failures;
            $message = trim($stdout . PHP_EOL . $stderr);
            fwrite(STDERR, sprintf(
                "PHP lint failed for %s:%s%s%s",
                $job['file'],
                PHP_EOL,
                $message,
                PHP_EOL
            ));
        }

        unset($running[$key]);
    }

    if ($running !== []) {
        usleep(10_000);
    }
}

if ($failures === 0) {
    fwrite(STDOUT, sprintf('PHP lint passed for %d files.%s', count($files), PHP_EOL));
    exit(0);
}

fwrite(STDERR, sprintf('PHP lint failed for %d file(s).%s', $failures, PHP_EOL));
exit(1);

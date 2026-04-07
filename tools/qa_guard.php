<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$command = $argv[1] ?? '';

$commands = [
    'analyse' => ['vendor/bin/phpstan', 'analyse', '--configuration=phpstan.neon.dist'],
    'lint:cs' => ['vendor/bin/php-cs-fixer', 'fix', '--dry-run', '--diff', '--config=.php-cs-fixer.dist.php'],
    'lint:cs:fix' => ['vendor/bin/php-cs-fixer', 'fix', '--config=.php-cs-fixer.dist.php'],
    'docs:openapi:dump:public' => ['bin/console', 'nelmio:apidoc:dump', '--area=public_discovery', '--format=json'],
    'docs:openapi:dump:management' => ['bin/console', 'nelmio:apidoc:dump', '--area=management_discovery', '--format=json'],
];

if (!isset($commands[$command])) {
    fwrite(STDERR, "Unknown QA command.\n");
    exit(2);
}

$binaryPath = $projectRoot . '/' . $commands[$command][0];
if (!is_file($binaryPath)) {
    fwrite(STDERR, sprintf("Required binary or entrypoint is missing: %s\n", $commands[$command][0]));
    exit(1);
}

$argvParts = array_map(static fn (string $part): string => escapeshellarg($part), $commands[$command]);
$process = implode(' ', array_merge(['php'], $argvParts));
passthru('cd ' . escapeshellarg($projectRoot) . ' && ' . $process, $exitCode);
exit($exitCode);

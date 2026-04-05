<?php

declare(strict_types=1);

$mode = $argv[1] ?? '';
$root = dirname(__DIR__);
$commands = [
    'phpstan' => [$root . '/vendor/bin/phpstan', 'analyse', '--configuration=' . $root . '/phpstan.neon.dist'],
    'cs-check' => [$root . '/vendor/bin/php-cs-fixer', 'fix', '--config=' . $root . '/.php-cs-fixer.dist.php', '--dry-run', '--diff', '--using-cache=no'],
    'cs-fix' => [$root . '/vendor/bin/php-cs-fixer', 'fix', '--config=' . $root . '/.php-cs-fixer.dist.php', '--using-cache=no'],
    'openapi-public' => [$root . '/bin/console', 'nelmio:apidoc:dump', '--area=public_discovery', '--format=json'],
    'openapi-management' => [$root . '/bin/console', 'nelmio:apidoc:dump', '--area=management_discovery', '--format=json'],
];

if (!isset($commands[$mode])) {
    fwrite(STDERR, "Unknown QA mode.
");
    exit(1);
}

$command = $commands[$mode];
$binary = $command[0];
if (!is_file($binary)) {
    fwrite(STDERR, sprintf("Required tool is not installed for mode '%s'.
", $mode));
    exit(2);
}

$process = proc_open(array_merge([PHP_BINARY], $command), [STDIN, STDOUT, STDERR], $pipes, $root);
if (!is_resource($process)) {
    fwrite(STDERR, "Unable to start QA process.
");
    exit(1);
}

exit(proc_close($process));

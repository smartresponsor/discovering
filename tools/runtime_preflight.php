<?php

declare(strict_types=1);

/**
 * Minimal repository-local runtime preflight that does not depend on vendor/autoload.
 *
 * Usage:
 *   php tools/runtime_preflight.php
 *   php tools/runtime_preflight.php --require-vendor
 *   php tools/runtime_preflight.php --json
 */

$requireVendor = in_array('--require-vendor', $argv, true);
$jsonOutput = in_array('--json', $argv, true);
$projectRoot = dirname(__DIR__);

$checks = [];
$failures = 0;

$addCheck = static function (string $name, bool $ok, string $detail) use (&$checks, &$failures): void {
    $checks[] = [
        'name' => $name,
        'ok' => $ok,
        'detail' => $detail,
    ];

    if (!$ok) {
        ++$failures;
    }
};

$phpVersion = PHP_VERSION;
$addCheck(
    'php_version',
    version_compare($phpVersion, '8.4.0', '>='),
    sprintf('Detected PHP %s; required >= 8.4.0.', $phpVersion)
);

foreach (['json', 'pdo', 'pdo_sqlite'] as $extension) {
    $addCheck(
        'ext_' . $extension,
        extension_loaded($extension),
        sprintf('Extension %s is %s.', $extension, extension_loaded($extension) ? 'loaded' : 'missing')
    );
}

$composerBinary = trim((string) shell_exec('command -v composer 2>/dev/null'));
$addCheck(
    'composer_binary',
    $composerBinary !== '',
    $composerBinary !== '' ? sprintf('Composer detected at %s.', $composerBinary) : 'Composer binary is not available in PATH.'
);

$requiredPaths = [
    'composer_json' => $projectRoot . '/composer.json',
    'kernel' => $projectRoot . '/src/Kernel.php',
    'console_entrypoint' => $projectRoot . '/bin/console',
    'http_entrypoint' => $projectRoot . '/public/index.php',
    'phpunit_config' => $projectRoot . '/phpunit.xml.dist',
    'framework_config' => $projectRoot . '/config/packages/framework.yaml',
];

foreach ($requiredPaths as $name => $path) {
    $addCheck(
        $name,
        is_file($path),
        is_file($path) ? sprintf('Found %s.', substr($path, strlen($projectRoot) + 1)) : sprintf('Missing %s.', substr($path, strlen($projectRoot) + 1))
    );
}

if ($requireVendor) {
    $autoloadPath = $projectRoot . '/vendor/autoload.php';
    $addCheck(
        'vendor_autoload',
        is_file($autoloadPath),
        is_file($autoloadPath) ? 'Found vendor/autoload.php.' : 'Missing vendor/autoload.php; run composer install.'
    );
}

$result = [
    'projectRoot' => $projectRoot,
    'requireVendor' => $requireVendor,
    'ok' => $failures === 0,
    'failures' => $failures,
    'checks' => $checks,
];

if ($jsonOutput) {
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit($failures === 0 ? 0 : 1);
}

fwrite(STDOUT, "Discovering runtime preflight\n");
fwrite(STDOUT, sprintf("Project root: %s\n", $projectRoot));
fwrite(STDOUT, sprintf("Vendor required: %s\n\n", $requireVendor ? 'yes' : 'no'));

foreach ($checks as $check) {
    fwrite(STDOUT, sprintf("[%s] %s — %s\n", $check['ok'] ? 'OK' : 'FAIL', $check['name'], $check['detail']));
}

fwrite(STDOUT, PHP_EOL);
fwrite(STDOUT, sprintf("Result: %s (%d failure%s)\n", $failures === 0 ? 'PASS' : 'FAIL', $failures, $failures === 1 ? '' : 's'));

exit($failures === 0 ? 0 : 1);

<?php

declare(strict_types=1);

/**
 * Repository-local runtime preflight.
 *
 * Default mode validates repository entrypoints and required files only.
 * Stricter environment checks are opt-in so structural repository checks do
 * not fail only because the local machine lacks Composer or optional PHP
 * extensions.
 *
 * Usage:
 *   php tools/runtime_preflight.php
 *   php tools/runtime_preflight.php --check-runtime-extensions
 *   php tools/runtime_preflight.php --require-composer
 *   php tools/runtime_preflight.php --require-vendor
 *   php tools/runtime_preflight.php --test-runtime
 *   php tools/runtime_preflight.php --json
 */

$requireComposer = in_array('--require-composer', $argv, true);
$requireVendor = in_array('--require-vendor', $argv, true);
$checkRuntimeExtensions = in_array('--check-runtime-extensions', $argv, true) || $requireVendor;
$requireTestRuntime = in_array('--test-runtime', $argv, true);
$jsonOutput = in_array('--json', $argv, true);
$projectRoot = dirname(__DIR__);

$checks = [];
$failures = 0;

$addCheck = static function (string $nameEntity, bool $ok, string $detail) use (&$checks, &$failures): void {
    $checks[] = [
        'nameEntity' => $nameEntity,
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

if ($checkRuntimeExtensions) {
    foreach (['json', 'pdo', 'pdo_sqlite'] as $extension) {
        $addCheck(
            'ext_' . $extension,
            extension_loaded($extension),
            sprintf('Extension %s is %s.', $extension, extension_loaded($extension) ? 'loaded' : 'missing')
        );
    }
}

if ($requireTestRuntime) {
    foreach (['dom', 'mbstring', 'xml', 'xmlwriter'] as $extension) {
        $addCheck(
            'ext_' . $extension,
            extension_loaded($extension),
            sprintf('Extension %s is %s.', $extension, extension_loaded($extension) ? 'loaded' : 'missing')
        );
    }
}

$composerBinary = '';
if (PHP_OS_FAMILY === 'Windows') {
    $composerBinary = trim((string) shell_exec('where composer 2>NUL'));
} else {
    $composerBinary = trim((string) shell_exec('command -v composer 2>/dev/null'));
}

if ($requireComposer || $requireVendor) {
    $addCheck(
        'composer_binary',
        $composerBinary !== '',
        $composerBinary !== '' ? sprintf('Composer detected at %s.', $composerBinary) : 'Composer binary is not available in PATH.'
    );
} else {
    $checks[] = [
        'nameEntity' => 'composer_binary',
        'ok' => $composerBinary !== '',
        'detail' => $composerBinary !== ''
            ? sprintf('Composer detected at %s.', $composerBinary)
            : 'Composer binary not checked as required; pass --require-composer or --require-vendor to enforce it.',
    ];
}

$requiredPaths = [
    'composer_json' => $projectRoot . '/composer.json',
    'kernel' => $projectRoot . '/src/Kernel.php',
    'console_entrypoint' => $projectRoot . '/bin/console',
    'http_entrypoint' => $projectRoot . '/public/index.php',
    'phpunit_config' => $projectRoot . '/phpunit.xml.dist',
    'framework_config' => $projectRoot . '/config/packages/framework.yaml',
];

foreach ($requiredPaths as $nameEntity => $path) {
    $addCheck(
        $nameEntity,
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
    'requireComposer' => $requireComposer,
    'requireVendor' => $requireVendor,
    'checkRuntimeExtensions' => $checkRuntimeExtensions,
    'requireTestRuntime' => $requireTestRuntime,
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
fwrite(STDOUT, sprintf("Composer required: %s\n", $requireComposer || $requireVendor ? 'yes' : 'no'));
fwrite(STDOUT, sprintf("Vendor required: %s\n", $requireVendor ? 'yes' : 'no'));
fwrite(STDOUT, sprintf("Runtime extensions required: %s\n", $checkRuntimeExtensions ? 'yes' : 'no'));
fwrite(STDOUT, sprintf("Test runtime required: %s\n\n", $requireTestRuntime ? 'yes' : 'no'));

foreach ($checks as $check) {
    fwrite(STDOUT, sprintf("[%s] %s — %s\n", $check['ok'] ? 'OK' : 'INFO', $check['nameEntity'], $check['detail']));
}

fwrite(STDOUT, PHP_EOL);
fwrite(STDOUT, sprintf("Result: %s (%d failure%s)\n", $failures === 0 ? 'PASS' : 'FAIL', $failures, $failures === 1 ? '' : 's'));

exit($failures === 0 ? 0 : 1);

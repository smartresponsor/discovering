<?php

declare(strict_types=1);

/**
 * Runs Discovering local structural gates without requiring Composer scripts.
 *
 * This wrapper intentionally avoids Composer autoload. It is suitable for
 * repository-shape validation on a fresh checkout or a partially provisioned
 * machine.
 */

$projectRoot = dirname(__DIR__);

$commands = [
    'runtime_preflight' => [PHP_BINARY, $projectRoot . '/tools/runtime_preflight.php'],
    'security_preflight' => [PHP_BINARY, $projectRoot . '/tools/security_preflight.php'],
    'canon_audit' => [PHP_BINARY, $projectRoot . '/tools/discovering_canon_audit.php'],
    'lint_php' => [PHP_BINARY, $projectRoot . '/tools/lint_php.php'],
    'docblock_policy' => [PHP_BINARY, $projectRoot . '/tools/docblock_policy_check.php'],
];

$failures = [];

foreach ($commands as $nameEntity => $command) {
    fwrite(STDOUT, sprintf("\n== %s ==\n", $nameEntity));

    $process = proc_open(
        $command,
        [
            0 => ['pipe', 'r'],
            1 => STDOUT,
            2 => STDERR,
        ],
        $pipes,
        $projectRoot
    );

    if (!is_resource($process)) {
        $failures[] = sprintf('%s: failed to start process', $nameEntity);
        continue;
    }

    fclose($pipes[0]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        $failures[] = sprintf('%s: exited with %d', $nameEntity, $exitCode);
    }
}

fwrite(STDOUT, "\nDiscovering local CI result: " . ($failures === [] ? 'PASS' : 'FAIL') . "\n");

if ($failures !== []) {
    fwrite(STDERR, "Failures:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

exit(0);

<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$coverageDir = $root.'/var/coverage';
if (!is_dir($coverageDir) && !mkdir($coverageDir, 0777, true) && !is_dir($coverageDir)) {
    throw new RuntimeException('Unable to create coverage directory.');
}

$run = static function (array $command) use ($root): string {
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start evidence command.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if (0 !== $exit) {
        throw new RuntimeException(sprintf("Evidence command failed (%d): %s", $exit, trim((string) $stderr)));
    }

    return (string) $stdout;
};

$run([PHP_BINARY, 'vendor/bin/phpunit', '--testsuite', 'Behavioral']);
$run([PHP_BINARY, 'vendor/bin/phpunit', '--testsuite', 'Functional']);

$routerJson = $run([PHP_BINARY, 'bin/console', 'debug:router', '--format=json', '--env=test', '--no-interaction']);
$routes = json_decode($routerJson, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($routes)) {
    throw new RuntimeException('Router inventory is not an object.');
}

$eligibleFunctional = [];
foreach ($routes as $name => $route) {
    if (is_string($name) && str_starts_with($name, 'app_')) {
        $eligibleFunctional[] = 'route:'.$name;
    }
}
sort($eligibleFunctional);

$functionalSource = '';
$functionalIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/tests/Functional'));
foreach ($functionalIterator as $file) {
    if ($file->isFile() && 'php' === strtolower($file->getExtension())) {
        $functionalSource .= file_get_contents($file->getPathname()) ?: '';
    }
}

preg_match_all("/['\"](\/[A-Za-z0-9_\-\/{}?.=&:]+)['\"]/", $functionalSource, $urlMatches);
$urls = array_values(array_unique($urlMatches[1] ?? []));

$coveredFunctional = [];
foreach ($routes as $name => $route) {
    if (!is_string($name) || !str_starts_with($name, 'app_') || !is_array($route)) {
        continue;
    }
    $regex = $route['pathRegex'] ?? null;
    if (!is_string($regex) || '' === $regex) {
        continue;
    }
    foreach ($urls as $url) {
        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && 1 === @preg_match($regex, $path)) {
            $coveredFunctional[] = 'route:'.$name;
            break;
        }
    }
}
$coveredFunctional = array_values(array_unique($coveredFunctional));
sort($coveredFunctional);

$behavioralMethods = [];
$behavioralIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/tests/Behavioral'));
foreach ($behavioralIterator as $file) {
    if (!$file->isFile() || 'php' !== strtolower($file->getExtension())) {
        continue;
    }
    $source = file_get_contents($file->getPathname()) ?: '';
    preg_match_all('/public function (test[A-Za-z0-9_]+)\s*\(/', $source, $matches);
    foreach ($matches[1] ?? [] as $method) {
        $behavioralMethods[] = 'workflow:'.$method;
    }
}
$behavioralMethods = array_values(array_unique($behavioralMethods));
sort($behavioralMethods);

$uiEligible = [
    'surface:public-discovery',
    'surface:management-overview',
];
$uiCovered = [];
if (in_array('route:app_discovery_index', $coveredFunctional, true)) {
    $uiCovered[] = 'surface:public-discovery';
}
if (in_array('route:app_management_discovery_overview', $coveredFunctional, true)) {
    $uiCovered[] = 'surface:management-overview';
}

$criticalEligible = [
    'workflow:management-rebuild',
    'workflow:rollback-execute',
];
$criticalCovered = [];
if (str_contains($functionalSource, 'testManagementRebuildReturnsEvidenceSummary')) {
    $criticalCovered[] = 'workflow:management-rebuild';
}
if (str_contains($functionalSource, 'testManagementRollbackExecutePromotesRollbackTarget')) {
    $criticalCovered[] = 'workflow:rollback-execute';
}

$evidence = [
    'schema' => 'behavioral-ui-coverage-v2',
    'generatedAt' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
    'producer' => [
        'kind' => 'repository_script',
        'script' => 'test:behavioral-coverage',
    ],
    'dimensions' => [
        'functional' => ['eligible' => $eligibleFunctional, 'covered' => $coveredFunctional],
        'behavioral' => ['eligible' => $behavioralMethods, 'covered' => $behavioralMethods],
        'ui' => ['eligible' => $uiEligible, 'covered' => $uiCovered],
        'critical' => ['eligible' => $criticalEligible, 'covered' => $criticalCovered],
    ],
];

file_put_contents(
    $coverageDir.'/behavioral-ui.json',
    json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
);

printf(
    "Behavioral/UI evidence written: functional %d/%d, behavioral %d/%d, ui %d/%d, critical %d/%d.\n",
    count($coveredFunctional),
    count($eligibleFunctional),
    count($behavioralMethods),
    count($behavioralMethods),
    count($uiCovered),
    count($uiEligible),
    count($criticalCovered),
    count($criticalEligible),
);


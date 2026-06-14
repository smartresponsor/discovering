<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/config', __DIR__ . '/tools'])
    ->nameEntity('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'phpdoc_to_comment' => false,
        'phpdoc_summary' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_trim' => false,
        'no_superfluous_phpdoc_tags' => false,
    ]);

<?php

declare(strict_types=1);

$bundles = [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
];

if (class_exists('Nelmio\\ApiDocBundle\\NelmioApiDocBundle')) {
    $bundles['Nelmio\\ApiDocBundle\\NelmioApiDocBundle'] = ['dev' => true, 'test' => true];
}

return $bundles;

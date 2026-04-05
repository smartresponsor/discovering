<?php

declare(strict_types=1);

$bundles = [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
];

if (class_exists(Nelmio\ApiDocBundle\NelmioApiDocBundle::class)) {
    $bundles[Nelmio\ApiDocBundle\NelmioApiDocBundle::class] = ['dev' => true, 'test' => true];
}

return $bundles;

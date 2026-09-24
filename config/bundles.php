<?php

declare(strict_types=1);

$bundles = [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle::class => ['all' => true],
    App\Collectioning\CollectioningBundle::class => ['all' => true],
    App\Tabling\TablingBundle::class => ['all' => true],
    App\Interfacing\InterfacingBundle::class => ['all' => true],
    App\Viewing\ViewingBundle::class => ['all' => true],
    App\Objecting\ObjectBundle::class => ['all' => true],
    App\Discovering\DiscoveringBundle::class => ['all' => true],
];

if (class_exists('Nelmio\\ApiDocBundle\\NelmioApiDocBundle')) {
    $bundles['Nelmio\\ApiDocBundle\\NelmioApiDocBundle'] = ['dev' => true, 'test' => true];
}

return $bundles;

<?php

declare(strict_types=1);

use Nelmio\ApiDocBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (!class_exists(\Nelmio\ApiDocBundle\NelmioApiDocBundle::class)) {
        return;
    }

    $container->extension('nelmio_api_doc', [
        'documentation' => [
            'info' => [
                'title' => 'Discovering API',
                'description' => 'Symfony-oriented discovery component API documentation.',
                'version' => '1.0.0',
            ],
        ],
        'areas' => [
            'public_discovery' => [
                'path_patterns' => ['^/api/(v1/)?discovery'],
            ],
            'management_discovery' => [
                'path_patterns' => ['^/management/discovery'],
            ],
        ],
    ]);
};

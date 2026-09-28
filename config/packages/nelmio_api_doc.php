<?php

declare(strict_types=1);

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (!class_exists(NelmioApiDocBundle::class)) {
        return;
    }

    $container->extension('nelmio_api_doc', [
        'html_config' => [
            'assets_mode' => 'bundle',
        ],
        'documentation' => [
            'info' => [
                'title' => 'Discovering API',
                'description' => 'Discovery public and management HTTP surfaces.',
                'version' => '1.0.0',
            ],
        ],
        'areas' => [
            'default' => [
                'path_patterns' => ['^/api/(?:v1/)?discovery'],
            ],
            'public_discovery' => [
                'path_patterns' => ['^/api/(?:v1/)?discovery'],
            ],
            'management_discovery' => [
                'path_patterns' => ['^/management/discovery'],
            ],
        ],
    ]);
};

<?php

declare(strict_types=1);

use Nelmio\ApiDocBundle\Controller\DocumentationController;
use Nelmio\ApiDocBundle\Controller\SwaggerUiController;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    if (!class_exists(NelmioApiDocBundle::class)) {
        return;
    }

    $routes->add('app_nelmio_api_doc_public_ui', '/api/doc/public-discovery')
        ->controller([SwaggerUiController::class, 'index'])
        ->defaults(['area' => 'public_discovery']);

    $routes->add('app_nelmio_api_doc_public_json', '/api/doc/public-discovery.json')
        ->controller([DocumentationController::class, 'index'])
        ->defaults(['area' => 'public_discovery']);

    $routes->add('app_nelmio_api_doc_management_ui', '/api/doc/management-discovery')
        ->controller([SwaggerUiController::class, 'index'])
        ->defaults(['area' => 'management_discovery']);

    $routes->add('app_nelmio_api_doc_management_json', '/api/doc/management-discovery.json')
        ->controller([DocumentationController::class, 'index'])
        ->defaults(['area' => 'management_discovery']);
};

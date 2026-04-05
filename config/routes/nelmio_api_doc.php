<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    if (!class_exists(\Nelmio\ApiDocBundle\NelmioApiDocBundle::class)) {
        return;
    }

    $routes->add('app_nelmio_api_doc_public', '/api/doc/public-discovery')
        ->controller('nelmio_api_doc.controller.swagger_ui')
        ->methods(['GET'])
        ->defaults(['area' => 'public_discovery']);

    $routes->add('app_nelmio_api_doc_management', '/api/doc/management-discovery')
        ->controller('nelmio_api_doc.controller.swagger_ui')
        ->methods(['GET'])
        ->defaults(['area' => 'management_discovery']);

    $routes->add('app_nelmio_api_doc_public_json', '/api/doc/public-discovery.json')
        ->controller('nelmio_api_doc.controller.swagger')
        ->methods(['GET'])
        ->defaults(['area' => 'public_discovery']);

    $routes->add('app_nelmio_api_doc_management_json', '/api/doc/management-discovery.json')
        ->controller('nelmio_api_doc.controller.swagger')
        ->methods(['GET'])
        ->defaults(['area' => 'management_discovery']);
};

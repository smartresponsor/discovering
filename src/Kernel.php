<?php
declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;


/**
 * Bootstraps the Discovering Symfony application kernel and its runtime environment.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir() . '/config';

        $loader->load($configDir . '/packages/*.yaml', 'glob');

        $environmentPackagesDir = $configDir . '/packages/' . $this->environment;
        if (is_dir($environmentPackagesDir)) {
            $loader->load($environmentPackagesDir . '/*.yaml', 'glob');
        }

        $loader->load($configDir . '/services.yaml');

        $environmentServices = $configDir . '/services_' . $this->environment . '.yaml';
        if (is_file($environmentServices)) {
            $loader->load($environmentServices);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getProjectDir() . '/config/routes';
        $routes->import($configDir . '/*.yaml');

        $environmentRoutesDir = $configDir . '/' . $this->environment;
        if (is_dir($environmentRoutesDir)) {
            $routes->import($environmentRoutesDir . '/*.yaml');
        }
    }
}

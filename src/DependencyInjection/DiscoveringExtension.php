<?php

declare(strict_types=1);

namespace App\Discovering\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Contributes Discovering host-side integration configuration.
 */
final class DiscoveringExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Prepends the Discovering-owned Twig override path before shared Interfacing templates.
     */
    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $container->prependExtensionConfig('twig', [
            'paths' => [
                \dirname(__DIR__, 2).'/templates/interfacing' => 'Interfacing',
            ],
        ]);
    }

    /**
     * Keeps the bundle extension load hook explicit while runtime services remain application-configured.
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }
}

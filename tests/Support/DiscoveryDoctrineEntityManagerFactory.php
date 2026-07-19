<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Support;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;

/**
 * Builds isolated Doctrine entity managers for Discovering persistence tests.
 */
final class DiscoveryDoctrineEntityManagerFactory
{
    public static function create(): EntityManagerInterface
    {
        $config = ORMSetup::createAttributeMetadataConfig([
            dirname(__DIR__, 2).'/src/Entity',
        ]);
        $config->enableNativeLazyObjects(true);

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        return new EntityManager($connection, $config);
    }
}

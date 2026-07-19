<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Entity\Discovery\DiscoverySearchLogEntity;
use App\Objecting\Embeddable\ObjectIdentityEmbeddable;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class DiscoveringObjectIdentityContractTest extends TestCase
{
    public function testSearchLogUsesCanonicalObjectIdentityContract(): void
    {
        $searchLog = new DiscoverySearchLogEntity('identity contract');
        $objectUuid = $searchLog->getObjectUuid();

        self::assertSame(26, \strlen($objectUuid));
        self::assertInstanceOf(UuidV7::class, Uuid::fromString($objectUuid));
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $objectUuid);
        self::assertSame($objectUuid, $searchLog->getObjectSlug());

        $searchLog->setObjectSlug('discovery-search-log');

        self::assertSame($objectUuid, $searchLog->getObjectUuid());
        self::assertSame('discovery-search-log', $searchLog->getObjectSlug());
    }

    public function testSearchLogMappingUsesBinaryUuidMandatorySlugAndSeparatePrimaryKey(): void
    {
        $configuration = ORMSetup::createAttributeMetadataConfiguration([], true);
        $configuration->enableNativeLazyObjects(true);
        $driverChain = new MappingDriverChain();

        $discoveringDriver = ORMSetup::createAttributeMetadataConfiguration([
            dirname(__DIR__, 3).'/src/Entity',
        ], true)->getMetadataDriverImpl();
        self::assertNotNull($discoveringDriver);
        $driverChain->addDriver($discoveringDriver, 'App\\Entity');

        $objectingDriver = ORMSetup::createAttributeMetadataConfiguration([
            dirname(__DIR__, 4).'/Objecting/src/Embeddable',
        ], true)->getMetadataDriverImpl();
        self::assertNotNull($objectingDriver);
        $driverChain->addDriver($objectingDriver, 'App\\Objecting\\Embeddable');
        $configuration->setMetadataDriverImpl($driverChain);

        $entityManager = new EntityManager(DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $configuration), $configuration);

        $metadata = $entityManager->getClassMetadata(DiscoverySearchLogEntity::class);
        $objectingMetadata = $entityManager->getClassMetadata(ObjectIdentityEmbeddable::class);

        self::assertSame(['id'], $metadata->getIdentifierFieldNames());
        self::assertTrue($objectingMetadata->isEmbeddedClass);

        $objectUuid = $metadata->getFieldMapping('objectIdentity.objectUuid');
        $objectSlug = $metadata->getFieldMapping('objectIdentity.objectSlug');

        self::assertArrayHasKey('objectIdentity', $metadata->embeddedClasses);
        self::assertSame('object_uuid', $objectUuid['columnName']);
        self::assertSame('binary', $objectUuid['type']);
        self::assertSame(16, $objectUuid['length']);
        self::assertFalse($objectUuid['nullable'] ?? false);
        self::assertSame('object_slug', $objectSlug['columnName']);
        self::assertSame('string', $objectSlug['type']);
        self::assertSame(190, $objectSlug['length']);
        self::assertFalse($objectSlug['nullable'] ?? false);
    }
}

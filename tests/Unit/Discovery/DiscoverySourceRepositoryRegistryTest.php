<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\DiscoverySourceRepositoryRegistry;
use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use PHPUnit\Framework\TestCase;

final class DiscoverySourceRepositoryRegistryTest extends TestCase
{
    public function testItReturnsRepositoryBySourceName(): void
    {
        $registry = new DiscoverySourceRepositoryRegistry([
            new ProjectDiscoverySourceRecordRepository(),
            new OfferingDiscoverySourceRecordRepository(),
            new DocumentDiscoverySourceRecordRepository(),
            new CategoryDiscoverySourceRecordRepository(),
        ]);

        $repository = $registry->getBySourceName('document-source-provider');

        self::assertSame(DocumentDiscoverySourceRecordRepository::class, $repository::class);
    }
}

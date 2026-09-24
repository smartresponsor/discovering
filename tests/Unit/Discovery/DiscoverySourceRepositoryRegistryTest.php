<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\Source\DiscoveryCategorySourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryDocumentSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryOfferingSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryProjectSourceRecordRepository;
use App\Discovering\Service\Source\Repository\DiscoverySourceRepositoryRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery source repository registry test case for the Discovering component.
 */
final class DiscoverySourceRepositoryRegistryTest extends TestCase
{
    public function testItReturnsRepositoryBySourceName(): void
    {
        $registry = new DiscoverySourceRepositoryRegistry([
            new DiscoveryProjectSourceRecordRepository(),
            new DiscoveryOfferingSourceRecordRepository(),
            new DiscoveryDocumentSourceRecordRepository(),
            new DiscoveryCategorySourceRecordRepository(),
        ]);

        $repository = $registry->getBySourceName('document-source-provider');

        self::assertSame(DiscoveryDocumentSourceRecordRepository::class, $repository::class);
    }
}

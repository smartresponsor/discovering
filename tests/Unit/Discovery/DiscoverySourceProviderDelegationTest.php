<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use PHPUnit\Framework\TestCase;

final class DiscoverySourceProviderDelegationTest extends TestCase
{
    public function testProjectProviderDelegatesToRepository(): void
    {
        $provider = new ProjectDiscoverySourceProvider(new ProjectDiscoverySourceRecordRepository());

        self::assertSame('project-source-provider', $provider->getSourceName());
        self::assertSame('project', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testOfferingProviderDelegatesToRepository(): void
    {
        $provider = new OfferingDiscoverySourceProvider(new OfferingDiscoverySourceRecordRepository());

        self::assertSame('offering-source-provider', $provider->getSourceName());
        self::assertSame('offering', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testDocumentProviderDelegatesToRepository(): void
    {
        $provider = new DocumentDiscoverySourceProvider(new DocumentDiscoverySourceRecordRepository());

        self::assertSame('document-source-provider', $provider->getSourceName());
        self::assertSame('document', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testCategoryProviderDelegatesToRepository(): void
    {
        $provider = new CategoryDiscoverySourceProvider(new CategoryDiscoverySourceRecordRepository());

        self::assertSame('category-source-provider', $provider->getSourceName());
        self::assertSame('category', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }
}

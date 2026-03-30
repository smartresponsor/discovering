<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Document\DiscoveryDocumentFactory;
use App\Service\Discovery\Document\DiscoveryDocumentProvider;
use App\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use PHPUnit\Framework\TestCase;

final class DiscoveryDocumentProviderTest extends TestCase
{
    public function testItBuildsDocumentsFromAllSourceProviders(): void
    {
        $provider = new DiscoveryDocumentProvider(
            sourceProviders: [
                new ProjectDiscoverySourceProvider(),
                new OfferingDiscoverySourceProvider(),
                new DocumentDiscoverySourceProvider(),
                new CategoryDiscoverySourceProvider(),
            ],
            documentFactory: new DiscoveryDocumentFactory(),
        );

        $documents = $provider->provide();

        self::assertCount(8, $documents);
        self::assertSame('project', $documents[0]->resourceType);
        self::assertSame('category', $documents[7]->resourceType);
    }

    public function testItFiltersByResourceTypeAcrossSourceProviders(): void
    {
        $provider = new DiscoveryDocumentProvider(
            sourceProviders: [
                new ProjectDiscoverySourceProvider(),
                new OfferingDiscoverySourceProvider(),
                new DocumentDiscoverySourceProvider(),
                new CategoryDiscoverySourceProvider(),
            ],
            documentFactory: new DiscoveryDocumentFactory(),
        );

        $documents = $provider->provide('offering');

        self::assertCount(2, $documents);
        self::assertSame('offering', $documents[0]->resourceType);
        self::assertSame('offering', $documents[1]->resourceType);
    }
}

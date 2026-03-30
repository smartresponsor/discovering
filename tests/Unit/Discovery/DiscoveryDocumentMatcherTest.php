<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\Document\StaticDiscoveryDocumentProvider;
use App\Service\Discovery\Support\DiscoveryDocumentMatcher;
use PHPUnit\Framework\TestCase;

final class DiscoveryDocumentMatcherTest extends TestCase
{
    public function testItFiltersByVisibilityAndStatus(): void
    {
        $matcher = new DiscoveryDocumentMatcher();
        $provider = new StaticDiscoveryDocumentProvider();

        $result = $matcher->match(
            $provider->provide('document'),
            new DiscoveryQuery(status: 'published', visibility: 'internal'),
        );

        self::assertSame(1, $result->total);
        self::assertSame('document-discovery-product-manifest', $result->hits[0]->resourceId);
    }

    public function testItReturnsNoDraftDocumentsForPublishedPublicFilter(): void
    {
        $matcher = new DiscoveryDocumentMatcher();
        $provider = new StaticDiscoveryDocumentProvider();

        $result = $matcher->match(
            $provider->provide('document'),
            new DiscoveryQuery(status: 'published', visibility: 'public'),
        );

        self::assertSame(0, $result->total);
    }
}

<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\DiscoveryHighlightingService;
use PHPUnit\Framework\TestCase;

final class DiscoveryHighlightingServiceTest extends TestCase
{
    public function testItHighlightsMatchedTokensSafely(): void
    {
        $service = new DiscoveryHighlightingService();

        $highlighted = $service->highlight('Search portability <briefing>', ['search', 'briefing']);

        self::assertStringContainsString('<mark>Search</mark>', $highlighted);
        self::assertStringContainsString('&lt;<mark>briefing</mark>&gt;', $highlighted);
    }
}

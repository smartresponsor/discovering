<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\DiscoveryHighlightingService;
use PHPUnit\Framework\TestCase;


/**
 * Exercises the discovery highlighting service test case for the Discovering component.
 */
final class DiscoveryHighlightingServiceTest extends TestCase
{
    public function testItHighlightsMatchedTokensSafely(): void
    {
        $service = new DiscoveryHighlightingService();

        $highlighted = $service->highlight('Search portability <briefing>', ['search', 'briefing']);

        self::assertStringContainsString('<mark>Search</mark>', $highlighted);
        self::assertStringContainsString('&lt;<mark>briefing</mark>&gt;', $highlighted);
    }

    public function testItBuildsContextSnippetAroundMatchedToken(): void
    {
        $service = new DiscoveryHighlightingService();

        $snippet = $service->buildSnippet(
            'This is a long content block about governance review and audit readiness for the discovery engine.',
            ['governance'],
            18,
        );

        self::assertStringContainsString('governance review', strtolower($snippet));
        self::assertStringStartsWith('…', $snippet);
        self::assertStringEndsWith('…', $snippet);
    }
}

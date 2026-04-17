<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

use App\Service\Discovery\Http\DiscoveryApiContract;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exercises the discovery http assertion test helper for the Discovering component.
 */
trait DiscoveryHttpAssertionTrait
{
    protected function assertDiscoveryJsonEnvelope(
        array $payload,
        string $canonicalPath,
        ?string $schemaFamily = null,
        string|int|null $schemaVersion = null,
        bool $deprecatedAlias = false,
    ): void {
        self::assertArrayHasKey('ok', $payload);
        self::assertArrayHasKey('apiVersion', $payload);
        self::assertArrayHasKey('requestId', $payload);
        self::assertArrayHasKey('meta', $payload);
        self::assertIsArray($payload['meta']);
        self::assertSame(DiscoveryApiContract::API_VERSION, $payload['apiVersion']);

        $expectedSchemaFamily = $schemaFamily ?? DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY;
        $expectedSchemaVersion = $schemaVersion
            ?? (null === $schemaFamily ? DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION : 1);

        self::assertSame($expectedSchemaFamily, $payload['meta']['schemaFamily'] ?? null);
        self::assertSame($expectedSchemaVersion, $payload['meta']['schemaVersion'] ?? null);
        self::assertSame($canonicalPath, $payload['meta']['canonicalPath'] ?? null);
        self::assertSame($deprecatedAlias, $payload['meta']['deprecatedAlias'] ?? null);
    }

    protected function assertDiscoveryResponseHeaders(KernelBrowser $client): void
    {
        $headers = $client->getResponse()->headers;

        self::assertSame(DiscoveryApiContract::API_VERSION, $headers->get(DiscoveryApiContract::API_VERSION_HEADER));
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY, $headers->get(DiscoveryApiContract::SCHEMA_FAMILY_HEADER));
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION, $headers->get(DiscoveryApiContract::SCHEMA_VERSION_HEADER));
        self::assertTrue($headers->has(DiscoveryOperationLogger::REQUEST_ID_HEADER));
        self::assertNotSame('', (string) $headers->get(DiscoveryOperationLogger::REQUEST_ID_HEADER));
    }

    protected function assertDiscoverySecurityHeaders(Response $response, bool $assertFullPolicy = true): void
    {
        $headers = $response->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('no-referrer', $headers->get('Referrer-Policy'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertStringContainsString('no-store', (string) $headers->get('Cache-Control'));
        self::assertStringContainsString('private', (string) $headers->get('Cache-Control'));

        if ($assertFullPolicy) {
            self::assertSame('camera=(), microphone=(), geolocation=()', $headers->get('Permissions-Policy'));
            self::assertSame("default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'", $headers->get('Content-Security-Policy'));
        }
    }

    protected function assertRateLimitHeaders(Response $response, string $scope, int $limit, int $remaining = 0): void
    {
        $headers = $response->headers;

        self::assertSame($scope, $headers->get('X-RateLimit-Scope'));
        self::assertSame((string) $limit, $headers->get('X-RateLimit-Limit'));
        self::assertSame((string) $remaining, $headers->get('X-RateLimit-Remaining'));
        self::assertNotSame('', (string) $headers->get('X-RateLimit-Reset'));
        self::assertNotSame('', (string) $headers->get('Retry-After'));
    }
}

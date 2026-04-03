<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

final class DiscoverySecurityPostureTest extends AbstractDiscoveryWebTestCase
{
    public function testPublicDiscoveryPageCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);

        self::assertResponseIsSuccessful();

        $headers = $client->getResponse()->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('no-referrer', $headers->get('Referrer-Policy'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('camera=(), microphone=(), geolocation=()', $headers->get('Permissions-Policy'));
        self::assertSame("default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'", $headers->get('Content-Security-Policy'));
        self::assertSame('no-store, private', $headers->get('Cache-Control'));
    }

    public function testVersionedDiscoveryApiCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/v1/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);

        self::assertResponseIsSuccessful();

        $headers = $client->getResponse()->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('no-referrer', $headers->get('Referrer-Policy'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('no-store, private', $headers->get('Cache-Control'));
    }

    public function testManagementOverviewCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $headers = $client->getResponse()->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('no-store, private', $headers->get('Cache-Control'));
    }

    public function testUnauthorizedApiWriteStillCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request(
            'POST',
            '/api/v1/discovery/click',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'resource' => 'briefing',
                'id' => 'briefing-live-source-governance',
                'title' => 'Live source governance briefing',
                'reference' => 'briefing-live-source-governance',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(401);

        $headers = $client->getResponse()->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('no-referrer', $headers->get('Referrer-Policy'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('no-store, private', $headers->get('Cache-Control'));
    }
}

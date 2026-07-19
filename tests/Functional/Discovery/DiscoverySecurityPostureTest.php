<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Functional\Discovery;

/**
 * Exercises the discovery security posture test case for the Discovering component.
 */
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

        $this->assertDiscoverySecurityHeaders($client->getResponse());
    }

    public function testVersionedDiscoveryApiCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);

        self::assertResponseIsSuccessful();

        $this->assertDiscoverySecurityHeaders($client->getResponse(), assertFullPolicy: false);
    }

    public function testManagementOverviewCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createManagementClient();
        $this->requestManagement($client, 'GET', '/management/discovery');

        self::assertResponseIsSuccessful();

        $headers = $client->getResponse()->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('no-store, private', $headers->get('Cache-Control'));
    }

    public function testUnauthorizedApiWriteStillCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('POST', '/api/discovery/click', [], [], ['CONTENT_TYPE' => 'application/json'], $this->jsonRequestBody($this->discoveryClickPayload()));

        self::assertResponseStatusCodeSame(401);

        $this->assertDiscoverySecurityHeaders($client->getResponse(), assertFullPolicy: false);
    }
}

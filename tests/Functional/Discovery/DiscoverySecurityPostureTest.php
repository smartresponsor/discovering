<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

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
        $client->request('GET', '/api/v1/discovery', [
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

        $this->assertDiscoverySecurityHeaders($client->getResponse(), assertFullPolicy: false);
    }

    public function testUnauthorizedApiWriteStillCarriesResponseSecurityHeaders(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('POST', '/api/v1/discovery/click', [], [], ['CONTENT_TYPE' => 'application/json'], $this->jsonRequestBody($this->discoveryClickPayload()));

        self::assertResponseStatusCodeSame(401);

        $this->assertDiscoverySecurityHeaders($client->getResponse(), assertFullPolicy: false);
    }
}

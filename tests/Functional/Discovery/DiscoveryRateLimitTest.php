<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Functional\Discovery;

/**
 * Exercises the discovery rate limit test case for the Discovering component.
 */
final class DiscoveryRateLimitTest extends AbstractDiscoveryWebTestCase
{
    public function testVersionedApiQueryReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createDiscoveryClient();

        for ($attempt = 0; $attempt < 4; ++$attempt) {
            $client->request('GET', '/api/discovery', [
                'query' => 'governance',
                'resource' => 'briefing',
            ]);
        }

        self::assertResponseStatusCodeSame(429);

        $payload = $this->jsonResponsePayload($client);
        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/discovery');
        self::assertSame('discovery_rate_limited', $payload['error']['code']);
        self::assertSame('query', $payload['error']['details']['scope']);
        $this->assertRateLimitHeaders($client->getResponse(), 'query', 3);
    }

    public function testApiWriteReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createApiWriteClient();

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $this->requestApiWrite($client, 'POST', '/api/discovery/click', $this->discoveryClickPayload());
        }

        self::assertResponseStatusCodeSame(429);

        $payload = $this->jsonResponsePayload($client);
        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/discovery/click');
        self::assertSame('write', $payload['error']['details']['scope']);
        $this->assertRateLimitHeaders($client->getResponse(), 'write', 2);
    }

    public function testManagementMutationReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createManagementClient();

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $this->requestManagement($client, 'POST', '/management/discovery/rebuild');
        }

        self::assertResponseStatusCodeSame(429);

        $payload = $this->jsonResponsePayload($client);
        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/management/discovery/rebuild');
        self::assertSame('management_mutation', $payload['error']['details']['scope']);
        $this->assertRateLimitHeaders($client->getResponse(), 'management_mutation', 2);
    }
}

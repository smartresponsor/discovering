<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

final class DiscoveryRateLimitTest extends AbstractDiscoveryWebTestCase
{
    public function testVersionedApiQueryReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createDiscoveryClient();

        for ($attempt = 0; $attempt < 4; ++$attempt) {
            $client->request('GET', '/api/v1/discovery', [
                'query' => 'governance',
                'resource' => 'briefing',
            ]);
        }

        self::assertResponseStatusCodeSame(429);

        $payload = $this->jsonResponsePayload($client);
        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/v1/discovery');
        self::assertSame('discovery_rate_limited', $payload['error']['code']);
        self::assertSame('query', $payload['error']['details']['scope']);
        $this->assertRateLimitHeaders($client->getResponse(), 'query', 3);
    }

    public function testApiWriteReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createDiscoveryClient($this->apiWriteTokenServer());

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $client->request(
                'POST',
                '/api/v1/discovery/click',
                [],
                [],
                $this->apiWriteTokenServer() + ['CONTENT_TYPE' => 'application/json'],
                $this->jsonRequestBody($this->discoveryClickPayload()),
            );
        }

        self::assertResponseStatusCodeSame(429);

        $payload = $this->jsonResponsePayload($client);
        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/v1/discovery/click');
        self::assertSame('write', $payload['error']['details']['scope']);
        $this->assertRateLimitHeaders($client->getResponse(), 'write', 2);
    }

    public function testManagementMutationReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());
        }

        self::assertResponseStatusCodeSame(429);

        $payload = $this->jsonResponsePayload($client);
        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/management/discovery/rebuild');
        self::assertSame('management_mutation', $payload['error']['details']['scope']);
        $this->assertRateLimitHeaders($client->getResponse(), 'management_mutation', 2);
    }
}

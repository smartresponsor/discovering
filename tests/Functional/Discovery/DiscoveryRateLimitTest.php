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

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertFalse($payload['ok']);
        self::assertSame('discovery_rate_limited', $payload['error']['code']);
        self::assertSame('query', $payload['error']['details']['scope']);
        self::assertSame('query', $client->getResponse()->headers->get('X-RateLimit-Scope'));
        self::assertSame('3', $client->getResponse()->headers->get('X-RateLimit-Limit'));
        self::assertSame('0', $client->getResponse()->headers->get('X-RateLimit-Remaining'));
        self::assertNotSame('', (string) $client->getResponse()->headers->get('Retry-After'));
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
                json_encode([
                    'resource' => 'briefing',
                    'id' => 'briefing-live-source-governance',
                    'title' => 'Live source governance briefing',
                    'reference' => 'briefing-live-source-governance',
                ], JSON_THROW_ON_ERROR),
            );
        }

        self::assertResponseStatusCodeSame(429);

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertFalse($payload['ok']);
        self::assertSame('write', $payload['error']['details']['scope']);
        self::assertSame('2', $client->getResponse()->headers->get('X-RateLimit-Limit'));
        self::assertSame('0', $client->getResponse()->headers->get('X-RateLimit-Remaining'));
    }

    public function testManagementMutationReturns429AfterConfiguredBurst(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());
        }

        self::assertResponseStatusCodeSame(429);

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertFalse($payload['ok']);
        self::assertSame('management_mutation', $payload['error']['details']['scope']);
        self::assertSame('2', $client->getResponse()->headers->get('X-RateLimit-Limit'));
        self::assertSame('0', $client->getResponse()->headers->get('X-RateLimit-Remaining'));
    }
}

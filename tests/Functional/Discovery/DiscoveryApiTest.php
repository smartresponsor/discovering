<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;

final class DiscoveryApiTest extends AbstractDiscoveryWebTestCase
{
    public function testVersionedApiDiscoveryReturnsSeededBriefingHit(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/v1/discovery', [
            'query' => 'governance live source',
            'resource' => 'briefing',
            'mode' => 'governance',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $client->getResponse()->headers->get(DiscoveryJsonResponseFactory::API_VERSION_HEADER));
        self::assertTrue($client->getResponse()->headers->has('X-Request-Id'));
        self::assertNotSame('', (string) $client->getResponse()->headers->get('X-Request-Id'));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $payload['apiVersion']);
        self::assertFalse($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/v1/discovery', $payload['meta']['canonicalPath']);
        self::assertSame('briefing', $payload['data']['query']['resource']);
        self::assertGreaterThanOrEqual(1, $payload['data']['total']);
        self::assertSame('briefing-live-source-governance', $payload['data']['hits'][0]['id']);
        self::assertSame('Live source governance briefing', $payload['data']['hits'][0]['title']);
    }

    public function testLegacyApiAliasReturnsCanonicalMeta(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/discovery', [
            'query' => 'governance live source',
            'resource' => 'briefing',
        ]);

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/v1/discovery', $payload['meta']['canonicalPath']);
    }

    public function testApiClickRequiresWriteToken(): void
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

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse($payload['ok']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $payload['apiVersion']);
        self::assertSame('discovery_api_write_unauthorized', $payload['error']['code']);
        self::assertSame('Unauthorized discovery API write request.', $payload['error']['message']);
    }

    public function testVersionedApiClickRecordsFeedbackCount(): void
    {
        $client = $this->createDiscoveryClient($this->apiWriteTokenServer());
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

        self::assertResponseIsSuccessful();
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $client->getResponse()->headers->get(DiscoveryJsonResponseFactory::API_VERSION_HEADER));
        self::assertTrue($client->getResponse()->headers->has('X-Request-Id'));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('/api/v1/discovery/click', $payload['meta']['canonicalPath']);
        self::assertSame('briefing', $payload['data']['resource']);
        self::assertSame('briefing-live-source-governance', $payload['data']['id']);
        self::assertSame(1, $payload['data']['feedbackCount']);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

final class DiscoveryApiTest extends AbstractDiscoveryWebTestCase
{
    public function testApiDiscoveryReturnsSeededBriefingHit(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/discovery', [
            'query' => 'governance live source',
            'resource' => 'briefing',
            'mode' => 'governance',
        ]);

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('briefing', $payload['data']['query']['resource']);
        self::assertGreaterThanOrEqual(1, $payload['data']['total']);
        self::assertSame('briefing-live-source-governance', $payload['data']['hits'][0]['id']);
        self::assertSame('Live source governance briefing', $payload['data']['hits'][0]['title']);
    }

    public function testApiClickRecordsFeedbackCount(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request(
            'POST',
            '/api/discovery/click',
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

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('briefing', $payload['data']['resource']);
        self::assertSame('briefing-live-source-governance', $payload['data']['id']);
        self::assertSame(1, $payload['data']['feedbackCount']);
    }
}

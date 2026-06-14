<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

/**
 * Exercises the discovery api test case for the Discovering component.
 */
final class DiscoveryApiTest extends AbstractDiscoveryWebTestCase
{
    public function testVersionedApiDiscoveryReturnsSeededBriefingHit(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/discovery', [
            'query' => 'governance live source',
            'resource' => 'briefing',
            'mode' => 'governance',
        ]);

        self::assertResponseIsSuccessful();
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertTrue($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/discovery');
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
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        $this->assertDiscoveryJsonEnvelope($payload, '/api/discovery', deprecatedAlias: true);
    }

    public function testApiClickRequiresWriteToken(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('POST', '/api/discovery/click', [], [], ['CONTENT_TYPE' => 'application/json'], $this->jsonRequestBody($this->discoveryClickPayload()));

        self::assertResponseStatusCodeSame(401);
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertFalse($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/discovery/click');
        self::assertSame('discovery_api_write_unauthorized', $payload['error']['code']);
        self::assertSame('Unauthorized discovery API write request.', $payload['error']['message']);
    }

    public function testVersionedApiClickRecordsFeedbackCount(): void
    {
        $client = $this->createApiWriteClient();
        $this->requestApiWrite($client, 'POST', '/api/discovery/click', $this->discoveryClickPayload());

        self::assertResponseIsSuccessful();
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertTrue($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, '/api/discovery/click');
        self::assertSame('briefing', $payload['data']['resource']);
        self::assertSame('briefing-live-source-governance', $payload['data']['id']);
        self::assertSame(1, $payload['data']['feedbackCount']);
    }

    public function testApiClickRejectsUnsupportedMutationContentType(): void
    {
        $client = $this->createApiWriteClient();
        $client->request('POST', '/api/discovery/click', [], [], $this->apiWriteTokenServer() + ['CONTENT_TYPE' => 'text/plain'], 'invalid');

        self::assertResponseStatusCodeSame(415);
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertFalse($payload['ok']);
        self::assertSame('discovery_mutation_unsupported_content_type', $payload['error']['code']);
    }

    public function testApiClickRejectsOversizedMutationPayload(): void
    {
        $client = $this->createApiWriteClient();
        $oversizedBody = str_repeat('x', 70000);
        $client->request('POST', '/api/discovery/click', [], [], $this->apiWriteTokenServer() + ['CONTENT_TYPE' => 'application/json', 'CONTENT_LENGTH' => (string) strlen($oversizedBody)], $oversizedBody);

        self::assertResponseStatusCodeSame(413);
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertFalse($payload['ok']);
        self::assertSame('discovery_mutation_payload_too_large', $payload['error']['code']);
    }
}

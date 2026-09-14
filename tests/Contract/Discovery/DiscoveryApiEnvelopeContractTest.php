<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Contract\Discovery;

use App\Discovering\Service\Discovery\Http\DiscoveryApiContract;
use App\Discovering\Tests\Functional\Discovery\AbstractDiscoveryWebTestCase;

/**
 * Exercises the discovery api envelope contract test case for the Discovering component.
 */
final class DiscoveryApiEnvelopeContractTest extends AbstractDiscoveryWebTestCase
{
    use DiscoveryApiContractAssertions;

    public function testVersionedDiscoveryEndpointRespectsEnvelopeContract(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/v1/discovery', [
            'query' => 'governance live source',
            'resource' => 'briefing',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(DiscoveryApiContract::API_VERSION, $client->getResponse()->headers->get(DiscoveryApiContract::API_VERSION_HEADER));
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY, $client->getResponse()->headers->get(DiscoveryApiContract::SCHEMA_FAMILY_HEADER));
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION, $client->getResponse()->headers->get(DiscoveryApiContract::SCHEMA_VERSION_HEADER));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertDiscoveryEnvelopeContract($payload);
        self::assertFalse($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/v1/discovery', $payload['meta']['canonicalPath']);
    }

    public function testLegacyDiscoveryAliasAdvertisesCanonicalSchemaContract(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/discovery', [
            'query' => 'governance live source',
            'resource' => 'briefing',
        ]);

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertDiscoveryEnvelopeContract($payload);
        self::assertTrue($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/v1/discovery', $payload['meta']['canonicalPath']);
    }

    public function testWriteAuthorizationErrorRespectsEnvelopeContract(): void
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

        self::assertResponseStatusCodeSame(401);
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION, $client->getResponse()->headers->get(DiscoveryApiContract::SCHEMA_VERSION_HEADER));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertDiscoveryEnvelopeContract($payload);
        self::assertFalse($payload['ok']);
        self::assertSame('discovery_api_write_unauthorized', $payload['error']['code']);
    }
}

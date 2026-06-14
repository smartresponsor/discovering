<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Http\DiscoveryApiContract;
use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Exercises the discovery json response factory test case for the Discovering component.
 */
final class DiscoveryJsonResponseFactoryTest extends TestCase
{
    public function testSuccessBuildsVersionedEnvelopeForLegacyAlias(): void
    {
        $request = Request::create('/api/discovery');
        $request->attributes->set(DiscoveryOperationLogger::REQUEST_ID_ATTRIBUTE, 'req-test-123');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $factory = new DiscoveryJsonResponseFactory($requestStack);
        $response = $factory->success(['ok' => 'value']);

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $payload['apiVersion']);
        self::assertSame('req-test-123', $payload['requestId']);
        self::assertTrue($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/discovery', $payload['meta']['canonicalPath']);
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY, $payload['meta']['schemaFamily']);
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION, $payload['meta']['schemaVersion']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $response->headers->get(DiscoveryJsonResponseFactory::API_VERSION_HEADER));
    }

    public function testErrorBuildsStructuredPayload(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/api/discovery/click'));

        $factory = new DiscoveryJsonResponseFactory($requestStack);
        $response = $factory->error('unauthorized', 'No token.', 401, ['header' => 'X-Discovery-Api-Write-Token']);

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse($payload['ok']);
        self::assertSame('unauthorized', $payload['error']['code']);
        self::assertSame('No token.', $payload['error']['message']);
        self::assertSame('X-Discovery-Api-Write-Token', $payload['error']['details']['header']);
        self::assertFalse($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/discovery/click', $payload['meta']['canonicalPath']);
    }

    public function testSuccessAllowsPayloadSchemaOverrideInMeta(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/management/discovery/rebuild'));

        $factory = new DiscoveryJsonResponseFactory($requestStack);
        $response = $factory->success(['resource' => 'global'], [
            'schemaFamily' => 'discovery.rebuild.summary',
            'schemaVersion' => 1,
        ]);

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('discovery.rebuild.summary', $payload['meta']['schemaFamily']);
        self::assertSame(1, $payload['meta']['schemaVersion']);
        self::assertSame('/management/discovery/rebuild', $payload['meta']['canonicalPath']);
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY, $response->headers->get(DiscoveryApiContract::SCHEMA_FAMILY_HEADER));
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION, $response->headers->get(DiscoveryApiContract::SCHEMA_VERSION_HEADER));
    }
}

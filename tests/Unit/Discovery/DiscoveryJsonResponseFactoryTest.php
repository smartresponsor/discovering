<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
        self::assertSame('/api/v1/discovery', $payload['meta']['canonicalPath']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $response->headers->get(DiscoveryJsonResponseFactory::API_VERSION_HEADER));
    }

    public function testErrorBuildsStructuredPayload(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/api/v1/discovery/click'));

        $factory = new DiscoveryJsonResponseFactory($requestStack);
        $response = $factory->error('unauthorized', 'No token.', 401, ['header' => 'X-Discovery-Api-Write-Token']);

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertFalse($payload['ok']);
        self::assertSame('unauthorized', $payload['error']['code']);
        self::assertSame('No token.', $payload['error']['message']);
        self::assertSame('X-Discovery-Api-Write-Token', $payload['error']['details']['header']);
        self::assertFalse($payload['meta']['deprecatedAlias']);
        self::assertSame('/api/v1/discovery/click', $payload['meta']['canonicalPath']);
    }
}

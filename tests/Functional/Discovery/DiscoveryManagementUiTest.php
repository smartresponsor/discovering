<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;

final class DiscoveryManagementUiTest extends AbstractDiscoveryWebTestCase
{
    public function testDiscoveryPageRendersWithSeededResult(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
            'mode' => 'governance',
        ]);

        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Discovering', $content);
        self::assertStringContainsString('Live source governance briefing', $content);
        self::assertStringContainsString('Mark useful', $content);
    }

    public function testManagementOverviewRequiresManagementToken(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/management/discovery');

        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('Forbidden discovery management request.', (string) $client->getResponse()->getContent());
    }

    public function testManagementOverviewExportReturnsCoverageSummary(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $client->getResponse()->headers->get(DiscoveryJsonResponseFactory::API_VERSION_HEADER));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $payload['apiVersion']);
        self::assertFalse($payload['meta']['deprecatedAlias']);
        self::assertSame('/management/discovery/export', $payload['meta']['canonicalPath']);
        self::assertSame('sqlite-fts5', $payload['data']['backendName']);
        self::assertGreaterThanOrEqual(1, $payload['data']['totalDocuments']);
        self::assertArrayHasKey('briefing', $payload['data']['countsByResourceType']);
        self::assertArrayHasKey('briefing-file-source-provider', $payload['data']['countsBySourceName']);
        self::assertNotEmpty($payload['data']['sampleDocuments']);
    }

    public function testManagementOperationsExportReturnsRecordedEvents(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/v1/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);

        $managementClient = $this->createDiscoveryClient($this->managementTokenServer());
        $managementClient->request('GET', '/management/discovery/operations/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();
        self::assertTrue($managementClient->getResponse()->headers->has('X-Request-Id'));

        $payload = json_decode((string) $managementClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertNotEmpty($payload['data']);
        self::assertContains('discovery.api.query', array_column($payload['data'], 'operation'));
    }

    public function testManagementOverviewPageRenders(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Discovery Management', $content);
        self::assertStringContainsString('Recent operations', $content);
        self::assertStringContainsString('Coverage by resource type', $content);
        self::assertStringContainsString('Coverage by source', $content);
    }
}

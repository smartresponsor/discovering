<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

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

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('sqlite-fts5', $payload['backendName']);
        self::assertGreaterThanOrEqual(1, $payload['totalDocuments']);
        self::assertArrayHasKey('briefing', $payload['countsByResourceType']);
        self::assertArrayHasKey('briefing-file-source-provider', $payload['countsBySourceName']);
        self::assertNotEmpty($payload['sampleDocuments']);
    }

    public function testManagementOverviewPageRenders(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Discovery Management', $content);
        self::assertStringContainsString('Coverage by resource type', $content);
        self::assertStringContainsString('Coverage by source', $content);
    }
}

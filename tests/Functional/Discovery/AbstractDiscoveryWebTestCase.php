<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Functional\Discovery;

use App\Discovering\DTO\DiscoveryReindexRequestDTO;
use App\Discovering\ServiceInterface\Indexer\DiscoveryIndexerInterface;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Exercises the abstract discovery web test case test case for the Discovering component.
 */
abstract class AbstractDiscoveryWebTestCase extends WebTestCase
{
    use DiscoveryHttpAssertionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();

        $client = static::createClient();
        /** @var DiscoveryIndexerInterface $indexer */
        $indexer = $client->getContainer()->get(DiscoveryIndexerInterface::class);
        $indexer->rebuild(new DiscoveryReindexRequestDTO());

        self::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        $this->resetConfiguredDiscoveryStorage();

        parent::tearDown();
    }

    /** @param array<string, string> $server */
    protected function createDiscoveryClient(array $server = []): KernelBrowser
    {
        self::ensureKernelShutdown();

        return static::createClient([], $server);
    }

    protected function createManagementClient(): KernelBrowser
    {
        return $this->createDiscoveryClient($this->managementTokenServer());
    }

    protected function createApiWriteClient(): KernelBrowser
    {
        return $this->createDiscoveryClient($this->apiWriteTokenServer());
    }

    /** @return array<string, string> */
    protected function managementTokenServer(): array
    {
        return [
            'HTTP_X_DISCOVERY_MANAGEMENT_TOKEN' => $this->envValue('APP_DISCOVERY_MANAGEMENT_TOKEN'),
        ];
    }

    /** @return array<string, string> */
    protected function apiWriteTokenServer(): array
    {
        return [
            'HTTP_X_DISCOVERY_API_WRITE_TOKEN' => $this->envValue('APP_DISCOVERY_API_WRITE_TOKEN'),
        ];
    }

    /** @return array<string, mixed> */
    protected function jsonResponsePayload(KernelBrowser $client): array
    {
        /** @var array<string, mixed> $payload */
        $payload = \json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    protected function jsonRequestBody(array $payload): string
    {
        return \json_encode($payload, \JSON_THROW_ON_ERROR);
    }

    /** @return array<string, mixed> */
    protected function discoveryClickPayload(): array
    {
        return [
            'resource' => 'briefing',
            'id' => 'briefing-live-source-governance',
            'title' => 'Live source governance briefing',
            'reference' => 'briefing-live-source-governance',
        ];
    }

    /** @param array<string, mixed> $parameters */
    protected function requestManagement(KernelBrowser $client, string $method, string $uri, array $parameters = []): void
    {
        $client->request($method, $uri, $parameters, [], $this->managementTokenServer());
    }

    protected function managementPageContent(string $uri = '/management/discovery'): string
    {
        $client = $this->createManagementClient();
        $this->requestManagement($client, 'GET', $uri);

        self::assertResponseIsSuccessful();

        return (string) $client->getResponse()->getContent();
    }

    /** @return array<string, mixed> */
    protected function managementExportPayload(
        string $uri,
        ?string $schemaFamily = null,
        string|int|null $schemaVersion = null,
        bool $deprecatedAlias = false,
    ): array {
        $client = $this->createManagementClient();
        $this->requestManagement($client, 'GET', $uri);

        self::assertResponseIsSuccessful();
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertTrue($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, $uri, $schemaFamily, $schemaVersion, $deprecatedAlias);

        return $payload;
    }

    /** @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    protected function managementMutationPayload(
        string $uri,
        array $parameters = [],
        ?string $schemaFamily = null,
        string|int|null $schemaVersion = null,
        bool $deprecatedAlias = false,
    ): array {
        $client = $this->createManagementClient();
        $this->requestManagement($client, 'POST', $uri, $parameters);

        self::assertResponseIsSuccessful();
        $this->assertDiscoveryResponseHeaders($client);

        $payload = $this->jsonResponsePayload($client);

        self::assertTrue($payload['ok']);
        $this->assertDiscoveryJsonEnvelope($payload, $uri, $schemaFamily, $schemaVersion, $deprecatedAlias);

        return $payload;
    }

    /** @param array<string, mixed> $parameters */
    protected function requestApiWrite(KernelBrowser $client, string $method, string $uri, array $parameters = []): void
    {
        $client->request(
            $method,
            $uri,
            [],
            [],
            $this->apiWriteTokenServer() + ['CONTENT_TYPE' => 'application/json'],
            $this->jsonRequestBody($parameters),
        );
    }

    /** @param array<string, mixed> $parameters */
    protected function requestPublicDiscoveryQuery(KernelBrowser $client, array $parameters = []): void
    {
        $client->request('GET', '/api/discovery', $parameters + [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);
    }

    protected function performManagementRebuilds(int $count): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        /** @var DiscoveryIndexerInterface $indexer */
        $indexer = $client->getContainer()->get(DiscoveryIndexerInterface::class);
        /** @var DiscoveryRebuildEvidenceStoreInterface $evidenceStore */
        $evidenceStore = $client->getContainer()->get(DiscoveryRebuildEvidenceStoreInterface::class);

        for ($attempt = 0; $attempt < $count; ++$attempt) {
            $evidenceStore->append($indexer->rebuild(new DiscoveryReindexRequestDTO()));
        }

        self::ensureKernelShutdown();
    }

    /** @return array<string, mixed> */
    protected function exportRollbackPlanPayload(): array
    {
        $client = $this->createManagementClient();
        $this->requestManagement($client, 'GET', '/management/discovery/rollback/export');

        return $this->jsonResponsePayload($client);
    }

    /** @return array<string, mixed> */
    protected function prepareRollbackScenarioPayload(): array
    {
        $this->performManagementRebuilds(2);

        return $this->exportRollbackPlanPayload();
    }

    /** @param array<string, mixed> $planPayload
     * @return array<string, mixed>
     */
    protected function executeRollbackPlanPayload(array $planPayload): array
    {
        return $this->managementMutationPayload(
            '/management/discovery/rollback/execute',
            [
                'current' => $planPayload['data']['currentEvidenceId'] ?? '',
                'target' => $planPayload['data']['previousEvidenceId'] ?? '',
            ],
            'discovery.rollback.execution',
        );
    }

    private function resetConfiguredDiscoveryStorage(): void
    {
        $directory = \dirname(__DIR__, 3).'/var/discovery';
        if (!\is_dir($directory)) {
            return;
        }

        $this->removeDirectoryContentsRecursively($directory);
    }

    private function removeDirectoryContentsRecursively(string $directory): void
    {
        $items = \scandir($directory);
        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory.'/'.$item;
            \clearstatcache(true, $path);

            if (\is_dir($path)) {
                $this->removeDirectoryContentsRecursively($path);
                @rmdir($path);
                continue;
            }

            if (\is_file($path)) {
                @\unlink($path);
            }
        }
    }

    private function envValue(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? \getenv($key);

        return is_string($value) ? $value : '';
    }
}

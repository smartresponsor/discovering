<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

use function array_filter;
use function array_values;
use function dirname;
use function getenv;
use function is_dir;
use function is_file;
use function is_string;
use function json_decode;
use function json_encode;
use function mkdir;
use function unlink;

use const JSON_THROW_ON_ERROR;


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
        self::bootKernel();
        $this->resetConfiguredDiscoveryStorage();
        self::ensureKernelShutdown();

        self::bootKernel();
        $indexer = static::getContainer()->get(DiscoveryIndexerInterface::class);
        $indexer->rebuild(new ReindexRequest());

        self::ensureKernelShutdown();
    }

    /** @param array<string, string> $server */
    protected function createDiscoveryClient(array $server = []): KernelBrowser
    {
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
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    protected function jsonRequestBody(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
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
     *  @return array<string, mixed>
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
        $client->request('GET', '/api/v1/discovery', $parameters + [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);
    }

    protected function performManagementRebuilds(int $count): void
    {
        $client = $this->createManagementClient();

        for ($attempt = 0; $attempt < $count; ++$attempt) {
            $this->requestManagement($client, 'POST', '/management/discovery/rebuild');
        }
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
     *  @return array<string, mixed>
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
        $container = static::getContainer();
        $parameterBag = $container->get('parameter_bag');
        if (!$parameterBag instanceof ContainerBagInterface) {
            return;
        }

        foreach ($this->configuredResettablePaths($parameterBag) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /** @return list<string> */
    private function configuredResettablePaths(ContainerBagInterface $parameterBag): array
    {
        $paths = [
            $this->stringParameter($parameterBag, 'app.discovery.default_sqlite_path'),
            $this->stringParameter($parameterBag, 'app.discovery.default_feedback_path'),
        ];

        if ('file' === $this->stringParameter($parameterBag, 'app.discovery.default_operation_log_backend')) {
            $paths[] = $this->stringParameter($parameterBag, 'app.discovery.default_operation_log_path');
        }

        if ('file' === $this->stringParameter($parameterBag, 'app.discovery.default_rebuild_evidence_backend')) {
            $paths[] = $this->stringParameter($parameterBag, 'app.discovery.default_rebuild_evidence_path');
        }

        if ('file' === $this->stringParameter($parameterBag, 'app.discovery.default_libsource_event_log_backend')) {
            $paths[] = $this->stringParameter($parameterBag, 'app.discovery.default_libsource_event_log_path');
        }

        if ('file' === $this->stringParameter($parameterBag, 'app.discovery.default_rate_limit_backend')) {
            $paths[] = $this->stringParameter($parameterBag, 'app.discovery.default_rate_limit_store_path');
        }

        $directory = dirname(__DIR__, 3) . '/var/discovery';
        if (!is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        /** @var list<string> $resolved */
        $resolved = array_values(array_filter($paths, static fn (string $path): bool => '' !== $path));

        return $resolved;
    }

    private function stringParameter(ContainerBagInterface $parameterBag, string $name): string
    {
        $value = $parameterBag->has($name) ? $parameterBag->get($name) : '';

        return is_string($value) ? $value : '';
    }

    private function envValue(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return is_string($value) ? $value : '';
    }
}

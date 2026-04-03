<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractDiscoveryWebTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->resetDiscoveryStorage();
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

    private function resetDiscoveryStorage(): void
    {
        $directory = dirname(__DIR__, 3) . '/var/discovery';
        if (!is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        foreach (['discovering.test.sqlite', 'discovering-feedback.test.sqlite', 'discovery-operation.test.json', 'discovery-rebuild-evidence.test.json', 'libsource-operator-event-log.test.json'] as $filename) {
            $path = $directory . '/' . $filename;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function envValue(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return is_string($value) ? $value : '';
    }
}

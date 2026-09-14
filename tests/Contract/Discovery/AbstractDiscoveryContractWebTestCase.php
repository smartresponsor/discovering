<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Contract\Discovery;

use App\Discovering\Tests\Functional\Discovery\DiscoveryHttpAssertionTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Provides the light discovery contract web test bootstrap for the Discovering component.
 */
abstract class AbstractDiscoveryContractWebTestCase extends WebTestCase
{
    use DiscoveryHttpAssertionTrait;

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

    private function envValue(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return is_string($value) ? $value : '';
    }
}

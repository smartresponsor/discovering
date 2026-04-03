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

    protected function createDiscoveryClient(): KernelBrowser
    {
        return static::createClient();
    }

    private function resetDiscoveryStorage(): void
    {
        $directory = dirname(__DIR__, 3) . '/var/discovery';
        if (!is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        foreach (['discovering.test.sqlite', 'discovering-feedback.test.sqlite'] as $filename) {
            $path = $directory . '/' . $filename;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}

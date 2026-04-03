<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryOperationEvent;
use App\Service\Discovery\Operations\DiscoveryOperationEventJsonSerializer;
use App\Service\Discovery\Operations\FileDiscoveryOperationEventLogStore;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class FileDiscoveryOperationEventLogStoreTest extends DiscoveryTempFilesystemTestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = $this->createTempFilePath('discovering-operation-log-', '.json');
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }

        parent::tearDown();
    }

    public function testAppendAndLatestPersistOperationEvents(): void
    {
        $store = new FileDiscoveryOperationEventLogStore($this->path, new DiscoveryOperationEventJsonSerializer());

        $store->append(new DiscoveryOperationEvent(
            requestId: 'req-1',
            channel: 'http',
            operation: 'discovery.api.query',
            status: 'ok',
            occurredAt: '2026-04-03T00:00:00+00:00',
            context: ['resource' => 'briefing'],
        ));
        $store->append(new DiscoveryOperationEvent(
            requestId: 'req-2',
            channel: 'console',
            operation: 'discovering.rebuild',
            status: 'ok',
            occurredAt: '2026-04-03T00:01:00+00:00',
            context: ['resource' => 'global'],
        ));

        $events = $store->all();
        self::assertCount(2, $events);
        self::assertSame('req-1', $events[0]->requestId);
        self::assertSame('discovering.rebuild', $events[1]->operation);

        $latest = $store->latest(1);
        self::assertCount(1, $latest);
        self::assertSame('req-2', $latest[0]->requestId);
    }
}

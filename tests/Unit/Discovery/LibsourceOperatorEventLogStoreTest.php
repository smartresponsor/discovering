<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\Service\Libsource\Log\DiscoveryEphemeralLibsourceOperatorEventLogStore;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the libsource operator event log store test case for the Discovering component.
 */
final class LibsourceOperatorEventLogStoreTest extends TestCase
{
    public function testEphemeralStoreAppendsAndClearsEvents(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:test', 'info', 'First event'));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:test', 'warning', 'Second event'));

        self::assertCount(2, $store->all());
        self::assertSame('First event', $store->all()[0]->summary);
        self::assertSame('Second event', $store->all()[1]->summary);

        $store->clear();

        self::assertSame([], $store->all());
    }
}

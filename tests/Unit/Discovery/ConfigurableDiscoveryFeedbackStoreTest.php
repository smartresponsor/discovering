<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\ConfigurableDiscoveryFeedbackStore;
use App\Service\Discovery\DiscoveryFeedbackStore;
use App\Service\Discovery\PdoDiscoveryFeedbackStore;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class ConfigurableDiscoveryFeedbackStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testItFallsBackToSqlitePathStoreWhenPdoBackendHasNoDsn(): void
    {
        $path = $this->createTempFilePath('discovering-feedback-configurable-', '.sqlite');
        $store = new ConfigurableDiscoveryFeedbackStore(
            sqliteStore: new DiscoveryFeedbackStore($path),
            pdoStore: new PdoDiscoveryFeedbackStore('', null, null, 'discovery_feedback_test'),
            backend: 'pdo',
            pdoDsn: '',
        );

        self::assertSame(1, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(1, $store->getClickCount('briefing', 'briefing-1'));

        @unlink($path);
    }

    public function testItUsesPdoStoreWhenConfigured(): void
    {
        $path = $this->createTempFilePath('discovering-feedback-configurable-unused-', '.sqlite');
        $store = new ConfigurableDiscoveryFeedbackStore(
            sqliteStore: new DiscoveryFeedbackStore($path),
            pdoStore: new PdoDiscoveryFeedbackStore('sqlite::memory:', null, null, 'discovery_feedback_test'),
            backend: 'pdo',
            pdoDsn: 'sqlite::memory:',
        );

        self::assertSame(1, $store->recordClick('briefing', 'briefing-2', 'Title', 'reference'));
        self::assertSame(1, $store->getClickCount('briefing', 'briefing-2'));
        self::assertSame(0, $store->getClickCount('briefing', 'missing-hit'));

        @unlink($path);
    }
}

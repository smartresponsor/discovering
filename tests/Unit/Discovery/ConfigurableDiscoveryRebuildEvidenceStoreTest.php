<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use App\Service\Discovery\Rebuild\ConfigurableDiscoveryRebuildEvidenceStore;
use App\Service\Discovery\Rebuild\DiscoveryRebuildEvidenceJsonSerializer;
use App\Service\Discovery\Rebuild\FileDiscoveryRebuildEvidenceStore;
use App\Service\Discovery\Rebuild\PdoDiscoveryRebuildEvidenceStore;
use PHPUnit\Framework\TestCase;

final class ConfigurableDiscoveryRebuildEvidenceStoreTest extends TestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = sys_get_temp_dir() . '/discovering-rebuild-evidence-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableDiscoveryRebuildEvidenceStore(
            new FileDiscoveryRebuildEvidenceStore($path, new DiscoveryRebuildEvidenceJsonSerializer()),
            new PdoDiscoveryRebuildEvidenceStore('', null, null, 'discovery_rebuild_evidence'),
            backend: 'file',
            pdoDsn: '',
        );

        $store->append(new DiscoveryRebuildSummary('ev-1', 'global', 'full', 'sqlite', 'in_place', false, '2026-04-03T18:00:00+00:00', '2026-04-03T18:00:05+00:00', 1, 1, 0));

        self::assertFileExists($path);
        self::assertCount(1, $store->latest(10));
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = sys_get_temp_dir() . '/discovering-rebuild-evidence-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableDiscoveryRebuildEvidenceStore(
            new FileDiscoveryRebuildEvidenceStore($path, new DiscoveryRebuildEvidenceJsonSerializer()),
            new PdoDiscoveryRebuildEvidenceStore('', null, null, 'discovery_rebuild_evidence'),
            backend: 'redis',
            pdoDsn: '',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery rebuild evidence backend');
        $store->latest(10);
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\Normalizer\Rebuild\DiscoveryRebuildEvidenceJsonSerializer;
use App\Discovering\Repository\Rebuild\DiscoveryDoctrineRebuildEvidenceStore;
use App\Discovering\Service\Rebuild\DiscoveryConfigurableRebuildEvidenceStore;
use App\Discovering\Service\Rebuild\DiscoveryFileRebuildEvidenceStore;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the configurable discovery rebuild evidence store test case for the Discovering component.
 */
final class ConfigurableDiscoveryRebuildEvidenceStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempFilePath('discovering-rebuild-evidence-', '.json');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableRebuildEvidenceStore(
            new DiscoveryFileRebuildEvidenceStore($path, new DiscoveryRebuildEvidenceJsonSerializer()),
            new DiscoveryDoctrineRebuildEvidenceStore($entityManager),
            backend: 'file',
        );

        $store->append(new DiscoveryRebuildSummaryDTO('ev-1', 'global', 'full', 'sqlite', 'in_place', false, '2026-04-03T18:00:00+00:00', '2026-04-03T18:00:05+00:00', 1, 1, 0));

        self::assertFileExists($path);
        self::assertCount(1, $store->latest(10));
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempFilePath('discovering-rebuild-evidence-', '.json');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableRebuildEvidenceStore(
            new DiscoveryFileRebuildEvidenceStore($path, new DiscoveryRebuildEvidenceJsonSerializer()),
            new DiscoveryDoctrineRebuildEvidenceStore($entityManager),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery rebuild evidence backend');
        $store->latest(10);
    }
}

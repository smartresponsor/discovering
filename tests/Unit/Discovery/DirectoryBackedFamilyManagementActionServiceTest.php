<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyManagementActionService;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the directory backed family management action service test case for the Discovering component.
 */
final class DirectoryBackedFamilyManagementActionServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItAuditsSeedsAndMigratesUsingSharedFamilyActionLogic(): void
    {
        $projectDir = $this->createTempDirectory('discovering-family-actions-');
        $legacyDirectory = $projectDir.'/resources/discovery';
        mkdir($legacyDirectory, 0777, true);
        file_put_contents($legacyDirectory.'/family_source_records.json', json_encode([
            ['resourceId' => 'legacy-family', 'title' => 'Legacy', 'body' => 'Legacy body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new class($projectDir, new DiscoverySourceRecordJsonFileDecoder(), new DiscoverySourceRecordJsonFileEncoder()) extends AbstractDirectoryBackedDiscoverySourceRecordRepository {
            public function getSourceName(): string
            {
                return 'family-file-source-provider';
            }

            public function getResourceType(): string
            {
                return 'family';
            }

            public function getStorageDirectoryPath(): string
            {
                return $this->getProjectDir().'/resources/discovery/family';
            }

            public function getStoragePath(): string
            {
                return $this->getStorageDirectoryPath().'/family_source_records.json';
            }

            public function getLegacyStoragePath(): string
            {
                return $this->getProjectDir().'/resources/discovery/family_source_records.json';
            }
        };

        $service = new DirectoryBackedFamilyManagementActionService(
            repository: $repository,
            familyLabel: 'family',
            sampleSeedDefinitions: [
                [
                    'fileName' => 'alpha.json',
                    'resourceId' => 'family-alpha',
                    'title' => 'Alpha family',
                    'body' => 'Alpha body',
                    'tags' => ['family', 'alpha'],
                ],
            ],
        );

        $audit = $service->auditRegistry();
        self::assertSame('audit-registry', $audit->actionName);
        self::assertTrue($audit->payload['legacyExists']);

        $migrate = $service->migrateLegacyStorage();
        self::assertTrue($migrate->payload['migrated']);
        self::assertFileDoesNotExist($repository->getLegacyStoragePath());
        self::assertFileExists($repository->getStoragePath());

        $seed = $service->ensureSampleRegistry();
        self::assertFalse($seed->payload['created']);

        @unlink($repository->getStoragePath());
        @rmdir($repository->getStorageDirectoryPath());
        @rmdir($projectDir.'/resources/discovery');
        @rmdir($projectDir.'/resources');
        @rmdir($projectDir);
    }
}

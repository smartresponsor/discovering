<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DirectoryBackedFamilyManagementActionResult;
use App\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Service\Discovery\Support\DirectoryBackedFamilyOperatorEventTrailBuilder;
use PHPUnit\Framework\TestCase;

final class DirectoryBackedFamilyOperatorEventTrailBuilderTest extends TestCase
{
    public function testItBuildsReusableOperatorTrail(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-family-trail-' . uniqid('', true);
        $storageDirectory = $projectDir . '/resources/discovery/family';
        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            ['resourceId' => 'family-alpha', 'title' => 'Alpha', 'body' => 'Alpha body'],
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
                return $this->getProjectDir() . '/resources/discovery/family';
            }

            public function getStoragePath(): string
            {
                return $this->getStorageDirectoryPath() . '/family_source_records.json';
            }

            public function getLegacyStoragePath(): string
            {
                return $this->getProjectDir() . '/resources/discovery/family_source_records.json';
            }
        };

        $builder = new DirectoryBackedFamilyOperatorEventTrailBuilder($repository, 'family');
        $events = $builder->build(new DirectoryBackedFamilyManagementActionResult(
            actionName: 'audit-registry',
            summary: 'Audited family registry.',
            payload: ['fileCount' => 1, 'recordCount' => 1],
        ));

        self::assertSame('surface:load', $events[0]->eventName);
        self::assertSame('info', $events[0]->level);
        self::assertSame('action:audit-registry', $events[1]->eventName);
        self::assertSame('registry:file', $events[2]->eventName);

        @unlink($storageDirectory . '/alpha.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}

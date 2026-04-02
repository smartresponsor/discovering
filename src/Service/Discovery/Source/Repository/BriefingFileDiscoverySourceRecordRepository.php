<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

final class BriefingFileDiscoverySourceRecordRepository extends AbstractDirectoryBackedDiscoverySourceRecordRepository
{
    public function getSourceName(): string
    {
        return 'briefing-file-source-provider';
    }

    public function getResourceType(): string
    {
        return 'briefing';
    }

    public function getStorageDirectoryPath(): string
    {
        return $this->getProjectDir() . '/resources/discovery/briefings';
    }

    public function getStoragePath(): string
    {
        return $this->getStorageDirectoryPath() . '/briefing_source_records.json';
    }

    public function getLegacyStoragePath(): string
    {
        return $this->getProjectDir() . '/resources/discovery/briefing_source_records.json';
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

/**
 * Provides briefing file discovery source record access for discovery source and management workflows.
 */
final class BriefingFileDiscoverySourceRecordRepository extends AbstractDirectoryBackedDiscoverySourceRecordRepository
{
    /**
     * Returns the source nameEntity value exposed by this service.
     */
    public function getSourceName(): string
    {
        return 'briefing-file-source-provider';
    }

    /**
     * Returns the resource type value exposed by this service.
     */
    public function getResourceType(): string
    {
        return 'briefing';
    }

    /**
     * Returns the storage directory path value exposed by this service.
     */
    public function getStorageDirectoryPath(): string
    {
        return $this->getProjectDir().'/resources/discovery/briefings';
    }

    /**
     * Returns the storage path value exposed by this service.
     */
    public function getStoragePath(): string
    {
        return $this->getStorageDirectoryPath().'/briefing_source_records.json';
    }

    /**
     * Returns the legacy storage path value exposed by this service.
     */
    public function getLegacyStoragePath(): string
    {
        return $this->getProjectDir().'/resources/discovery/briefing_source_records.json';
    }
}

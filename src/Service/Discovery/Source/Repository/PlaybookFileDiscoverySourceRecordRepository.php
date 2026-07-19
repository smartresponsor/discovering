<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Source\Repository;

/**
 * Provides playbook file discovery source record access for discovery source and management workflows.
 */
final class PlaybookFileDiscoverySourceRecordRepository extends AbstractDirectoryBackedDiscoverySourceRecordRepository
{
    /**
     * Returns the source nameEntity value exposed by this service.
     */
    public function getSourceName(): string
    {
        return 'playbook-file-source-provider';
    }

    /**
     * Returns the resource type value exposed by this service.
     */
    public function getResourceType(): string
    {
        return 'playbook';
    }

    /**
     * Returns the storage directory path value exposed by this service.
     */
    public function getStorageDirectoryPath(): string
    {
        return $this->getProjectDir().'/resources/discovery/playbooks';
    }

    /**
     * Returns the storage path value exposed by this service.
     */
    public function getStoragePath(): string
    {
        return $this->getStorageDirectoryPath().'/playbook_source_records.json';
    }

    /**
     * Returns the legacy storage path value exposed by this service.
     */
    public function getLegacyStoragePath(): string
    {
        return $this->getProjectDir().'/resources/discovery/playbook_source_records.json';
    }
}

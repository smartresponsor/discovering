<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

final class PlaybookFileDiscoverySourceRecordRepository extends AbstractDirectoryBackedDiscoverySourceRecordRepository
{
    public function getSourceName(): string
    {
        return 'playbook-file-source-provider';
    }

    public function getResourceType(): string
    {
        return 'playbook';
    }

    public function getStorageDirectoryPath(): string
    {
        return $this->getProjectDir() . '/resources/discovery/playbooks';
    }

    public function getStoragePath(): string
    {
        return $this->getStorageDirectoryPath() . '/playbook_source_records.json';
    }

    public function getLegacyStoragePath(): string
    {
        return $this->getProjectDir() . '/resources/discovery/playbook_source_records.json';
    }
}

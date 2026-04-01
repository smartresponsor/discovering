<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;

final class PlaybookFileDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    public function __construct(
        private readonly string $projectDir,
        private readonly DiscoverySourceRecordJsonFileDecoder $decoder,
        private readonly DiscoverySourceRecordJsonFileEncoder $encoder,
    ) {
    }

    public function getSourceName(): string
    {
        return 'playbook-file-source-provider';
    }

    public function getResourceType(): string
    {
        return 'playbook';
    }

    public function all(): array
    {
        return $this->decoder->decodeFile(
            $this->getStoragePath(),
            $this->getResourceType(),
        );
    }

    public function getStoragePath(): string
    {
        return $this->projectDir . '/resources/discovery/playbook_source_records.json';
    }

    public function exportJson(): string
    {
        return $this->encoder->encodeRecords($this->all());
    }

    /**
     * @param list<DiscoverySourceRecord> $records
     */
    public function replaceAll(array $records): void
    {
        $storagePath = $this->getStoragePath();
        $directory = dirname($storagePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($storagePath, $this->encoder->encodeRecords($records));
    }

    public function importFile(string $path): int
    {
        $records = $this->decoder->decodeFile($path, $this->getResourceType());
        $this->replaceAll($records);

        return count($records);
    }
}

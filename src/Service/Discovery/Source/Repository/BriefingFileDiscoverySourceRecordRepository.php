<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;

final class BriefingFileDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    public function __construct(
        private readonly string $projectDir,
        private readonly DiscoverySourceRecordJsonFileDecoder $decoder,
        private readonly DiscoverySourceRecordJsonFileEncoder $encoder,
    ) {
    }

    public function getSourceName(): string
    {
        return 'briefing-file-source-provider';
    }

    public function getResourceType(): string
    {
        return 'briefing';
    }

    public function all(): array
    {
        $records = [];

        foreach ($this->listStorageFiles() as $path) {
            $records = [...$records, ...$this->allFromStorageFile($path)];
        }

        return $records;
    }

    /**
     * @return list<string>
     */
    public function listStorageFiles(): array
    {
        $directoryPath = $this->getStorageDirectoryPath();

        if (is_dir($directoryPath)) {
            $matches = glob($directoryPath . '/*.json');
            $paths = is_array($matches) ? array_values(array_filter($matches, 'is_string')) : [];
            sort($paths);

            return $paths;
        }

        $legacyPath = $this->getLegacyStoragePath();

        return is_file($legacyPath) ? [$legacyPath] : [];
    }

    /**
     * @return list<DiscoverySourceRecord>
     */
    public function allFromStorageFile(string $path): array
    {
        return $this->decoder->decodeFile($path, $this->getResourceType());
    }

    public function getStorageDirectoryPath(): string
    {
        return $this->projectDir . '/resources/discovery/briefings';
    }

    public function getStoragePath(): string
    {
        return $this->getStorageDirectoryPath() . '/briefing_source_records.json';
    }

    public function getLegacyStoragePath(): string
    {
        return $this->projectDir . '/resources/discovery/briefing_source_records.json';
    }

    public function exportJson(): string
    {
        return $this->encoder->encodeRecords($this->all());
    }

    /**
     * @param list<DiscoverySourceRecord> $records
     */
    public function replaceAll(array $records, ?string $targetPath = null): void
    {
        $storagePath = $targetPath ?? $this->getStoragePath();
        $directory = dirname($storagePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($storagePath, $this->encoder->encodeRecords($records));
    }

    public function importFile(string $path, ?string $targetPath = null): int
    {
        $records = $this->decoder->decodeFile($path, $this->getResourceType());
        $this->replaceAll($records, $targetPath);

        return count($records);
    }
}

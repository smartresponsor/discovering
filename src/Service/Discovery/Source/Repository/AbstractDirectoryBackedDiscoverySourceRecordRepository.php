<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Source\Repository;

use App\Discovering\Dto\Discovery\DiscoverySourceRecord;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\ServiceInterface\Discovery\Source\Repository\DiscoverySourceRecordRepositoryInterface;

/**
 * Provides abstract directory backed discovery source record access for discovery source and management workflows.
 */
abstract class AbstractDirectoryBackedDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    public function __construct(
        private readonly string $projectDir,
        private readonly DiscoverySourceRecordJsonFileDecoder $decoder,
        private readonly DiscoverySourceRecordJsonFileEncoder $encoder,
    ) {
    }

    /**
     * Performs the all operation for this discovery service.
     */
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
            $matches = glob($directoryPath.'/*.json');
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

    /**
     * Performs the export json operation for this discovery service.
     */
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

    /**
     * Performs the import file operation for this discovery service.
     */
    public function importFile(string $path, ?string $targetPath = null): int
    {
        $records = $this->decoder->decodeFile($path, $this->getResourceType());
        $this->replaceAll($records, $targetPath);

        return count($records);
    }

    /**
     * Returns the project dir value exposed by this service.
     */
    protected function getProjectDir(): string
    {
        return $this->projectDir;
    }

    abstract public function getSourceName(): string;

    abstract public function getResourceType(): string;

    abstract public function getStorageDirectoryPath(): string;

    abstract public function getStoragePath(): string;

    abstract public function getLegacyStoragePath(): string;
}

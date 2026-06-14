<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DirectoryBackedFamilyManagementActionResult;
use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Support\DirectoryBackedFamilyManagementActionServiceInterface;

/**
 * Provides the directory backed family management action capability within the discovery component.
 */
final class DirectoryBackedFamilyManagementActionService implements DirectoryBackedFamilyManagementActionServiceInterface
{
    /**
     * @param list<array{
     * fileName: string,
     * resourceId: string,
     * title: string,
     * body: string,
     * tags: list<string>,
     * status?: string,
     * visibility?: string
     * }> $sampleSeedDefinitions
     */
    public function __construct(
        private readonly AbstractDirectoryBackedDiscoverySourceRecordRepository $repository,
        private readonly string $familyLabel,
        private readonly array $sampleSeedDefinitions,
    ) {
    }

    /**
     * Performs the audit registry operation for this discovery service.
     */
    public function auditRegistry(): DirectoryBackedFamilyManagementActionResult
    {
        $files = $this->repository->listStorageFiles();
        $records = $this->repository->all();
        $legacyPath = $this->repository->getLegacyStoragePath();
        $legacyExists = is_file($legacyPath);

        return new DirectoryBackedFamilyManagementActionResult(
            actionName: 'audit-registry',
            summary: sprintf('Audited %s registry: %d files, %d records, legacy=%s.', $this->familyLabel, count($files), count($records), $legacyExists ? 'yes' : 'no'),
            payload: [
                'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                'fileCount' => count($files),
                'recordCount' => count($records),
                'legacyStoragePath' => $legacyPath,
                'legacyExists' => $legacyExists,
                'files' => $files,
            ],
        );
    }

    /**
     * Performs the ensure sample registry operation for this discovery service.
     */
    public function ensureSampleRegistry(): DirectoryBackedFamilyManagementActionResult
    {
        $existingFiles = $this->repository->listStorageFiles();

        if ([] !== $existingFiles) {
            return new DirectoryBackedFamilyManagementActionResult(
                actionName: 'ensure-sample-registry',
                summary: sprintf('%s registry already has %d file(s); sample seeding skipped.', ucfirst($this->familyLabel), count($existingFiles)),
                payload: [
                    'created' => false,
                    'fileCount' => count($existingFiles),
                    'files' => $existingFiles,
                ],
            );
        }

        $directoryPath = $this->repository->getStorageDirectoryPath();
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        $createdFiles = [];
        foreach ($this->sampleSeedDefinitions as $definition) {
            $path = $directoryPath.'/'.$definition['fileName'];
            $createdFiles[] = $path;

            $this->repository->replaceAll([
                new DiscoverySourceRecord(
                    resourceType: $this->repository->getResourceType(),
                    resourceId: $definition['resourceId'],
                    title: $definition['title'],
                    body: $definition['body'],
                    filters: [
                        'status' => $definition['status'] ?? 'active',
                        'visibility' => $definition['visibility'] ?? 'internal',
                    ],
                    metadata: ['tags' => $definition['tags']],
                ),
            ], $path);
        }

        return new DirectoryBackedFamilyManagementActionResult(
            actionName: 'ensure-sample-registry',
            summary: sprintf('Seeded sample %s registry files.', $this->familyLabel),
            payload: [
                'created' => true,
                'files' => $createdFiles,
                'recordCount' => count($this->repository->all()),
            ],
        );
    }

    /**
     * Performs the migrate legacy storage operation for this discovery service.
     */
    public function migrateLegacyStorage(): DirectoryBackedFamilyManagementActionResult
    {
        $legacyPath = $this->repository->getLegacyStoragePath();

        if (!is_file($legacyPath)) {
            return new DirectoryBackedFamilyManagementActionResult(
                actionName: 'migrate-legacy-storage',
                summary: sprintf('No legacy %s storage file was found; migration skipped.', $this->familyLabel),
                payload: [
                    'migrated' => false,
                    'legacyStoragePath' => $legacyPath,
                ],
            );
        }

        $directoryFiles = [];
        foreach ($this->repository->listStorageFiles() as $path) {
            if ($path !== $legacyPath) {
                $directoryFiles[] = $path;
            }
        }

        if ([] !== $directoryFiles) {
            return new DirectoryBackedFamilyManagementActionResult(
                actionName: 'migrate-legacy-storage',
                summary: sprintf('Directory-backed %s registry already exists; legacy migration skipped to avoid overwrite.', $this->familyLabel),
                payload: [
                    'migrated' => false,
                    'legacyStoragePath' => $legacyPath,
                    'existingFiles' => $directoryFiles,
                ],
            );
        }

        $importedCount = $this->repository->importFile($legacyPath, $this->repository->getStoragePath());
        @unlink($legacyPath);

        return new DirectoryBackedFamilyManagementActionResult(
            actionName: 'migrate-legacy-storage',
            summary: sprintf('Migrated %d legacy %s records into the directory-backed registry.', $importedCount, $this->familyLabel),
            payload: [
                'migrated' => true,
                'importedCount' => $importedCount,
                'legacyStoragePath' => $legacyPath,
                'targetPath' => $this->repository->getStoragePath(),
            ],
        );
    }
}

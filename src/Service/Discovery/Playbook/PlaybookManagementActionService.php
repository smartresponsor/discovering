<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\Dto\Discovery\PlaybookManagementActionResult;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;

final class PlaybookManagementActionService
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    public function auditRegistry(): PlaybookManagementActionResult
    {
        $files = $this->repository->listStorageFiles();
        $records = $this->repository->all();
        $legacyPath = $this->repository->getLegacyStoragePath();
        $legacyExists = is_file($legacyPath);

        return new PlaybookManagementActionResult(
            actionName: 'audit-registry',
            summary: sprintf('Audited playbook registry: %d files, %d records, legacy=%s.', count($files), count($records), $legacyExists ? 'yes' : 'no'),
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

    public function ensureSampleRegistry(): PlaybookManagementActionResult
    {
        $existingFiles = $this->repository->listStorageFiles();

        if ($existingFiles !== []) {
            return new PlaybookManagementActionResult(
                actionName: 'ensure-sample-registry',
                summary: sprintf('Playbook registry already has %d file(s); sample seeding skipped.', count($existingFiles)),
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

        $operationsPath = $directoryPath . '/operations.json';
        $governancePath = $directoryPath . '/governance.json';

        $this->repository->replaceAll([
            new DiscoverySourceRecord(
                resourceType: 'playbook',
                resourceId: 'playbook-reindex-operations',
                title: 'Reindex operations playbook',
                body: 'Operational playbook for rebuilding the discovery index and validating post-reindex retrieval health.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['playbook', 'operations', 'reindex']],
            ),
        ], $operationsPath);

        $this->repository->replaceAll([
            new DiscoverySourceRecord(
                resourceType: 'playbook',
                resourceId: 'playbook-governance-audit',
                title: 'Governance audit playbook',
                body: 'Governance-oriented playbook for auditing source coverage, export flows, and operational integrity of live playbook records.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['playbook', 'governance', 'audit']],
            ),
        ], $governancePath);

        return new PlaybookManagementActionResult(
            actionName: 'ensure-sample-registry',
            summary: 'Seeded sample playbook registry files for operations and governance.',
            payload: [
                'created' => true,
                'files' => [$operationsPath, $governancePath],
                'recordCount' => count($this->repository->all()),
            ],
        );
    }

    public function migrateLegacyStorage(): PlaybookManagementActionResult
    {
        $legacyPath = $this->repository->getLegacyStoragePath();

        if (!is_file($legacyPath)) {
            return new PlaybookManagementActionResult(
                actionName: 'migrate-legacy-storage',
                summary: 'No legacy playbook storage file was found; migration skipped.',
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

        if ($directoryFiles !== []) {
            return new PlaybookManagementActionResult(
                actionName: 'migrate-legacy-storage',
                summary: 'Directory-backed playbook registry already exists; legacy migration skipped to avoid overwrite.',
                payload: [
                    'migrated' => false,
                    'legacyStoragePath' => $legacyPath,
                    'existingFiles' => $directoryFiles,
                ],
            );
        }

        $importedCount = $this->repository->importFile($legacyPath, $this->repository->getStoragePath());
        @unlink($legacyPath);

        return new PlaybookManagementActionResult(
            actionName: 'migrate-legacy-storage',
            summary: sprintf('Migrated %d legacy playbook records into the directory-backed registry.', $importedCount),
            payload: [
                'migrated' => true,
                'importedCount' => $importedCount,
                'legacyStoragePath' => $legacyPath,
                'targetPath' => $this->repository->getStoragePath(),
            ],
        );
    }
}

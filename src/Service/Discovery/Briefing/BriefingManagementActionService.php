<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementActionResult;
use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;

final class BriefingManagementActionService
{
    public function __construct(
        private readonly BriefingFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    public function auditRegistry(): BriefingManagementActionResult
    {
        $files = $this->repository->listStorageFiles();
        $records = $this->repository->all();
        $legacyPath = $this->repository->getLegacyStoragePath();
        $legacyExists = is_file($legacyPath);

        return new BriefingManagementActionResult(
            actionName: 'audit-registry',
            summary: sprintf('Audited briefing registry: %d files, %d records, legacy=%s.', count($files), count($records), $legacyExists ? 'yes' : 'no'),
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

    public function ensureSampleRegistry(): BriefingManagementActionResult
    {
        $existingFiles = $this->repository->listStorageFiles();

        if ($existingFiles !== []) {
            return new BriefingManagementActionResult(
                actionName: 'ensure-sample-registry',
                summary: sprintf('Briefing registry already has %d file(s); sample seeding skipped.', count($existingFiles)),
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

        $technologyPath = $directoryPath . '/technology.json';
        $governancePath = $directoryPath . '/governance.json';

        $this->repository->replaceAll([
            new DiscoverySourceRecord(
                resourceType: 'briefing',
                resourceId: 'briefing-search-portability',
                title: 'Search portability briefing',
                body: 'Concise briefing on portability expectations between local demo backends and future external discovery/search backends.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['briefing', 'technology', 'portability']],
            ),
        ], $technologyPath);

        $this->repository->replaceAll([
            new DiscoverySourceRecord(
                resourceType: 'briefing',
                resourceId: 'briefing-governance-review',
                title: 'Governance review briefing',
                body: 'Contextual briefing for operators reviewing live source families, registry layout, and merge-readiness signals.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['briefing', 'governance', 'review']],
            ),
        ], $governancePath);

        return new BriefingManagementActionResult(
            actionName: 'ensure-sample-registry',
            summary: 'Seeded sample briefing registry files for technology and governance.',
            payload: [
                'created' => true,
                'files' => [$technologyPath, $governancePath],
                'recordCount' => count($this->repository->all()),
            ],
        );
    }

    public function migrateLegacyStorage(): BriefingManagementActionResult
    {
        $legacyPath = $this->repository->getLegacyStoragePath();

        if (!is_file($legacyPath)) {
            return new BriefingManagementActionResult(
                actionName: 'migrate-legacy-storage',
                summary: 'No legacy briefing storage file was found; migration skipped.',
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
            return new BriefingManagementActionResult(
                actionName: 'migrate-legacy-storage',
                summary: 'Directory-backed briefing registry already exists; legacy migration skipped to avoid overwrite.',
                payload: [
                    'migrated' => false,
                    'legacyStoragePath' => $legacyPath,
                    'existingFiles' => $directoryFiles,
                ],
            );
        }

        $importedCount = $this->repository->importFile($legacyPath, $this->repository->getStoragePath());
        @unlink($legacyPath);

        return new BriefingManagementActionResult(
            actionName: 'migrate-legacy-storage',
            summary: sprintf('Migrated %d legacy briefing records into the directory-backed registry.', $importedCount),
            payload: [
                'migrated' => true,
                'importedCount' => $importedCount,
                'legacyStoragePath' => $legacyPath,
                'targetPath' => $this->repository->getStoragePath(),
            ],
        );
    }
}

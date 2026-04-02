<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementActionResult;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementActionService;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementActionServiceInterface;

final class PlaybookManagementActionService implements DirectoryBackedFamilyManagementActionServiceInterface
{
    private readonly DirectoryBackedFamilyManagementActionService $delegate;

    public function __construct(
        PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
        $this->delegate = new DirectoryBackedFamilyManagementActionService(
            repository: $repository,
            familyLabel: 'playbook',
            sampleSeedDefinitions: [
                [
                    'fileName' => 'operations.json',
                    'resourceId' => 'playbook-reindex-operations',
                    'title' => 'Reindex operations playbook',
                    'body' => 'Operational playbook for rebuilding the discovery index and validating post-reindex retrieval health.',
                    'tags' => ['playbook', 'operations', 'reindex'],
                ],
                [
                    'fileName' => 'governance.json',
                    'resourceId' => 'playbook-governance-audit',
                    'title' => 'Governance audit playbook',
                    'body' => 'Governance-oriented playbook for auditing source coverage, export flows, and operational integrity of live playbook records.',
                    'tags' => ['playbook', 'governance', 'audit'],
                ],
            ],
        );
    }

    public function auditRegistry(): PlaybookManagementActionResult
    {
        return PlaybookManagementActionResult::fromGeneric($this->delegate->auditRegistry());
    }

    public function ensureSampleRegistry(): PlaybookManagementActionResult
    {
        return PlaybookManagementActionResult::fromGeneric($this->delegate->ensureSampleRegistry());
    }

    public function migrateLegacyStorage(): PlaybookManagementActionResult
    {
        return PlaybookManagementActionResult::fromGeneric($this->delegate->migrateLegacyStorage());
    }
}

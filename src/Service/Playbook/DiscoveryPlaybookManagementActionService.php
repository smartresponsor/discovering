<?php

declare(strict_types=1);

namespace App\Discovering\Service\Playbook;

use App\Discovering\DTO\DiscoveryPlaybookManagementActionResultDTO;
use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;
use App\Discovering\Service\Support\DiscoveryDirectoryBackedFamilyManagementActionService;
use App\Discovering\ServiceInterface\Support\DiscoveryDirectoryBackedFamilyManagementActionServiceInterface;

/**
 * Provides the playbook management action capability within the discovery component.
 */
final class DiscoveryPlaybookManagementActionService implements DiscoveryDirectoryBackedFamilyManagementActionServiceInterface
{
    private readonly DiscoveryDirectoryBackedFamilyManagementActionService $delegate;

    public function __construct(
        DiscoveryPlaybookFileSourceRecordRepository $repository,
    ) {
        $this->delegate = new DiscoveryDirectoryBackedFamilyManagementActionService(
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

    /**
     * Performs the audit registry operation for this discovery service.
     */
    public function auditRegistry(): DiscoveryPlaybookManagementActionResultDTO
    {
        return DiscoveryPlaybookManagementActionResultDTO::fromGeneric($this->delegate->auditRegistry());
    }

    /**
     * Performs the ensure sample registry operation for this discovery service.
     */
    public function ensureSampleRegistry(): DiscoveryPlaybookManagementActionResultDTO
    {
        return DiscoveryPlaybookManagementActionResultDTO::fromGeneric($this->delegate->ensureSampleRegistry());
    }

    /**
     * Performs the migrate legacy storage operation for this discovery service.
     */
    public function migrateLegacyStorage(): DiscoveryPlaybookManagementActionResultDTO
    {
        return DiscoveryPlaybookManagementActionResultDTO::fromGeneric($this->delegate->migrateLegacyStorage());
    }
}

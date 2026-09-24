<?php

declare(strict_types=1);

namespace App\Discovering\Service\Briefing;

use App\Discovering\DTO\DiscoveryBriefingManagementActionResultDTO;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;
use App\Discovering\Service\Support\DiscoveryDirectoryBackedFamilyManagementActionService;
use App\Discovering\ServiceInterface\Support\DiscoveryDirectoryBackedFamilyManagementActionServiceInterface;

/**
 * Provides the briefing management action capability within the discovery component.
 */
final class DiscoveryBriefingManagementActionService implements DiscoveryDirectoryBackedFamilyManagementActionServiceInterface
{
    private readonly DiscoveryDirectoryBackedFamilyManagementActionService $delegate;

    public function __construct(
        DiscoveryBriefingFileSourceRecordRepository $repository,
    ) {
        $this->delegate = new DiscoveryDirectoryBackedFamilyManagementActionService(
            repository: $repository,
            familyLabel: 'briefing',
            sampleSeedDefinitions: [
                [
                    'fileName' => 'technology.json',
                    'resourceId' => 'briefing-search-portability',
                    'title' => 'Search portability briefing',
                    'body' => 'Concise briefing on portability expectations between local demo backends and future external discovery/search backends.',
                    'tags' => ['briefing', 'technology', 'portability'],
                ],
                [
                    'fileName' => 'governance.json',
                    'resourceId' => 'briefing-governance-review',
                    'title' => 'Governance review briefing',
                    'body' => 'Contextual briefing for operators reviewing live source families, registry layout, and merge-readiness signals.',
                    'tags' => ['briefing', 'governance', 'review'],
                ],
            ],
        );
    }

    /**
     * Performs the audit registry operation for this discovery service.
     */
    public function auditRegistry(): DiscoveryBriefingManagementActionResultDTO
    {
        return DiscoveryBriefingManagementActionResultDTO::fromGeneric($this->delegate->auditRegistry());
    }

    /**
     * Performs the ensure sample registry operation for this discovery service.
     */
    public function ensureSampleRegistry(): DiscoveryBriefingManagementActionResultDTO
    {
        return DiscoveryBriefingManagementActionResultDTO::fromGeneric($this->delegate->ensureSampleRegistry());
    }

    /**
     * Performs the migrate legacy storage operation for this discovery service.
     */
    public function migrateLegacyStorage(): DiscoveryBriefingManagementActionResultDTO
    {
        return DiscoveryBriefingManagementActionResultDTO::fromGeneric($this->delegate->migrateLegacyStorage());
    }
}

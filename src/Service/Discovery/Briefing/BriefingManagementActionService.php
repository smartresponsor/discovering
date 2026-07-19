<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Briefing;

use App\Discovering\Dto\Discovery\BriefingManagementActionResult;
use App\Discovering\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyManagementActionService;
use App\Discovering\ServiceInterface\Discovery\Support\DirectoryBackedFamilyManagementActionServiceInterface;

/**
 * Provides the briefing management action capability within the discovery component.
 */
final class BriefingManagementActionService implements DirectoryBackedFamilyManagementActionServiceInterface
{
    private readonly DirectoryBackedFamilyManagementActionService $delegate;

    public function __construct(
        BriefingFileDiscoverySourceRecordRepository $repository,
    ) {
        $this->delegate = new DirectoryBackedFamilyManagementActionService(
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
    public function auditRegistry(): BriefingManagementActionResult
    {
        return BriefingManagementActionResult::fromGeneric($this->delegate->auditRegistry());
    }

    /**
     * Performs the ensure sample registry operation for this discovery service.
     */
    public function ensureSampleRegistry(): BriefingManagementActionResult
    {
        return BriefingManagementActionResult::fromGeneric($this->delegate->ensureSampleRegistry());
    }

    /**
     * Performs the migrate legacy storage operation for this discovery service.
     */
    public function migrateLegacyStorage(): BriefingManagementActionResult
    {
        return BriefingManagementActionResult::fromGeneric($this->delegate->migrateLegacyStorage());
    }
}

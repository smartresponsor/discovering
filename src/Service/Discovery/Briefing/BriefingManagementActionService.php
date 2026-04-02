<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementActionResult;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementActionService;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementActionServiceInterface;

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

    public function auditRegistry(): BriefingManagementActionResult
    {
        return BriefingManagementActionResult::fromGeneric($this->delegate->auditRegistry());
    }

    public function ensureSampleRegistry(): BriefingManagementActionResult
    {
        return BriefingManagementActionResult::fromGeneric($this->delegate->ensureSampleRegistry());
    }

    public function migrateLegacyStorage(): BriefingManagementActionResult
    {
        return BriefingManagementActionResult::fromGeneric($this->delegate->migrateLegacyStorage());
    }
}

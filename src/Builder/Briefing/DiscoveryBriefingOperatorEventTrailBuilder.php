<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Briefing;

use App\Discovering\Builder\Support\DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder;
use App\Discovering\DTO\DiscoveryBriefingManagementActionResultDTO;
use App\Discovering\DTO\DiscoveryBriefingOperatorEventDTO;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;

/**
 * Builds the briefing operator event trail output used by discovery management or diagnostics flows.
 */
final class DiscoveryBriefingOperatorEventTrailBuilder
{
    private readonly DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder $delegate;

    public function __construct(
        DiscoveryBriefingFileSourceRecordRepository $repository,
    ) {
        $this->delegate = new DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder($repository, 'briefing');
    }

    /**
     * @return list<DiscoveryBriefingOperatorEventDTO>
     */
    public function build(?DiscoveryBriefingManagementActionResultDTO $lastActionResult = null): array
    {
        return array_map(
            static fn ($event): DiscoveryBriefingOperatorEventDTO => DiscoveryBriefingOperatorEventDTO::fromGeneric($event),
            $this->delegate->build($lastActionResult),
        );
    }
}

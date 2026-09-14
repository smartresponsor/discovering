<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Briefing;

use App\Discovering\Dto\Discovery\BriefingManagementActionResult;
use App\Discovering\Dto\Discovery\BriefingOperatorEvent;
use App\Discovering\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyOperatorEventTrailBuilder;

/**
 * Builds the briefing operator event trail output used by discovery management or diagnostics flows.
 */
final class BriefingOperatorEventTrailBuilder
{
    private readonly DirectoryBackedFamilyOperatorEventTrailBuilder $delegate;

    public function __construct(
        BriefingFileDiscoverySourceRecordRepository $repository,
    ) {
        $this->delegate = new DirectoryBackedFamilyOperatorEventTrailBuilder($repository, 'briefing');
    }

    /**
     * @return list<BriefingOperatorEvent>
     */
    public function build(?BriefingManagementActionResult $lastActionResult = null): array
    {
        return array_map(
            static fn ($event): BriefingOperatorEvent => BriefingOperatorEvent::fromGeneric($event),
            $this->delegate->build($lastActionResult),
        );
    }
}

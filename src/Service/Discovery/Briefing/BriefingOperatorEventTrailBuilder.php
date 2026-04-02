<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementActionResult;
use App\Dto\Discovery\BriefingOperatorEvent;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyOperatorEventTrailBuilder;

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

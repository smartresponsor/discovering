<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementActionResult;
use App\Dto\Discovery\PlaybookOperatorEvent;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyOperatorEventTrailBuilder;

final class PlaybookOperatorEventTrailBuilder
{
    private readonly DirectoryBackedFamilyOperatorEventTrailBuilder $delegate;

    public function __construct(
        PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
        $this->delegate = new DirectoryBackedFamilyOperatorEventTrailBuilder($repository, 'playbook');
    }

    /**
     * @return list<PlaybookOperatorEvent>
     */
    public function build(?PlaybookManagementActionResult $lastActionResult = null): array
    {
        return array_map(
            static fn ($event): PlaybookOperatorEvent => PlaybookOperatorEvent::fromGeneric($event),
            $this->delegate->build($lastActionResult),
        );
    }
}

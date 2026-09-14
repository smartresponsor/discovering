<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Playbook;

use App\Discovering\Dto\Discovery\PlaybookManagementActionResult;
use App\Discovering\Dto\Discovery\PlaybookOperatorEvent;
use App\Discovering\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyOperatorEventTrailBuilder;

/**
 * Builds the playbook operator event trail output used by discovery management or diagnostics flows.
 */
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

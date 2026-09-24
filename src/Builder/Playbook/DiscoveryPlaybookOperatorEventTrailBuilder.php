<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Playbook;

use App\Discovering\Builder\Support\DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder;
use App\Discovering\DTO\DiscoveryPlaybookManagementActionResultDTO;
use App\Discovering\DTO\DiscoveryPlaybookOperatorEventDTO;
use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;

/**
 * Builds the playbook operator event trail output used by discovery management or diagnostics flows.
 */
final class DiscoveryPlaybookOperatorEventTrailBuilder
{
    private readonly DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder $delegate;

    public function __construct(
        DiscoveryPlaybookFileSourceRecordRepository $repository,
    ) {
        $this->delegate = new DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder($repository, 'playbook');
    }

    /**
     * @return list<DiscoveryPlaybookOperatorEventDTO>
     */
    public function build(?DiscoveryPlaybookManagementActionResultDTO $lastActionResult = null): array
    {
        return array_map(
            static fn ($event): DiscoveryPlaybookOperatorEventDTO => DiscoveryPlaybookOperatorEventDTO::fromGeneric($event),
            $this->delegate->build($lastActionResult),
        );
    }
}

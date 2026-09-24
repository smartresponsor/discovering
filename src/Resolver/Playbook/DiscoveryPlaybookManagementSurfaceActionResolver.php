<?php

declare(strict_types=1);

namespace App\Discovering\Resolver\Playbook;

use App\Discovering\DTO\DiscoveryPlaybookManagementActionResultDTO;
use App\Discovering\Resolver\Support\DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver;
use App\Discovering\Service\Playbook\DiscoveryPlaybookManagementActionService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the playbook management surface action resolver capability within the discovery component.
 */
final class DiscoveryPlaybookManagementSurfaceActionResolver
{
    private readonly DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver $delegate;

    public function __construct(
        DiscoveryPlaybookManagementActionService $actionService,
    ) {
        $this->delegate = new DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver($actionService);
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?DiscoveryPlaybookManagementActionResultDTO
    {
        $result = $this->delegate->resolve($request);

        return null !== $result ? DiscoveryPlaybookManagementActionResultDTO::fromGeneric($result) : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Resolver\Briefing;

use App\Discovering\DTO\DiscoveryBriefingManagementActionResultDTO;
use App\Discovering\Resolver\Support\DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver;
use App\Discovering\Service\Briefing\DiscoveryBriefingManagementActionService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the briefing management surface action resolver capability within the discovery component.
 */
final class DiscoveryBriefingManagementSurfaceActionResolver
{
    private readonly DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver $delegate;

    public function __construct(
        DiscoveryBriefingManagementActionService $actionService,
    ) {
        $this->delegate = new DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver($actionService);
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?DiscoveryBriefingManagementActionResultDTO
    {
        $result = $this->delegate->resolve($request);

        return null !== $result ? DiscoveryBriefingManagementActionResultDTO::fromGeneric($result) : null;
    }
}

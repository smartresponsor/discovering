<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Briefing;

use App\Discovering\Dto\Discovery\BriefingManagementActionResult;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceActionResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the briefing management surface action resolver capability within the discovery component.
 */
final class BriefingManagementSurfaceActionResolver
{
    private readonly DirectoryBackedFamilyManagementSurfaceActionResolver $delegate;

    public function __construct(
        BriefingManagementActionService $actionService,
    ) {
        $this->delegate = new DirectoryBackedFamilyManagementSurfaceActionResolver($actionService);
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?BriefingManagementActionResult
    {
        $result = $this->delegate->resolve($request);

        return null !== $result ? BriefingManagementActionResult::fromGeneric($result) : null;
    }
}

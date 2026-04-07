<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementActionResult;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceActionResolver;
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

        return $result !== null ? BriefingManagementActionResult::fromGeneric($result) : null;
    }
}

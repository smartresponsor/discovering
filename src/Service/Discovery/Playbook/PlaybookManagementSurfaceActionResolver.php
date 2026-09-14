<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Playbook;

use App\Discovering\Dto\Discovery\PlaybookManagementActionResult;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceActionResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the playbook management surface action resolver capability within the discovery component.
 */
final class PlaybookManagementSurfaceActionResolver
{
    private readonly DirectoryBackedFamilyManagementSurfaceActionResolver $delegate;

    public function __construct(
        PlaybookManagementActionService $actionService,
    ) {
        $this->delegate = new DirectoryBackedFamilyManagementSurfaceActionResolver($actionService);
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?PlaybookManagementActionResult
    {
        $result = $this->delegate->resolve($request);

        return null !== $result ? PlaybookManagementActionResult::fromGeneric($result) : null;
    }
}

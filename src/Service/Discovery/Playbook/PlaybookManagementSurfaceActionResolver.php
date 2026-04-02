<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementActionResult;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceActionResolver;
use Symfony\Component\HttpFoundation\Request;

final class PlaybookManagementSurfaceActionResolver
{
    private readonly DirectoryBackedFamilyManagementSurfaceActionResolver $delegate;

    public function __construct(
        PlaybookManagementActionService $actionService,
    ) {
        $this->delegate = new DirectoryBackedFamilyManagementSurfaceActionResolver($actionService);
    }

    public function resolve(Request $request): ?PlaybookManagementActionResult
    {
        $result = $this->delegate->resolve($request);

        return $result !== null ? PlaybookManagementActionResult::fromGeneric($result) : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DirectoryBackedFamilyManagementActionResult;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides the directory backed family management surface action resolver capability within the discovery component.
 */
final class DirectoryBackedFamilyManagementSurfaceActionResolver
{
    public function __construct(
        private readonly DirectoryBackedFamilyManagementActionServiceInterface $actionService,
    ) {
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?DirectoryBackedFamilyManagementActionResult
    {
        $action = $request->query->get('action');

        if (!is_string($action) || $action === '') {
            return null;
        }

        return match ($action) {
            'audit-registry' => $this->actionService->auditRegistry(),
            'ensure-sample-registry' => $this->actionService->ensureSampleRegistry(),
            'migrate-legacy-storage' => $this->actionService->migrateLegacyStorage(),
            default => null,
        };
    }
}

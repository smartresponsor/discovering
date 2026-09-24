<?php

declare(strict_types=1);

namespace App\Discovering\Resolver\Support;

use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyManagementActionResultDTO;
use App\Discovering\ServiceInterface\Support\DiscoveryDirectoryBackedFamilyManagementActionServiceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the directory backed family management surface action resolver capability within the discovery component.
 */
final class DiscoveryDirectoryBackedFamilyManagementSurfaceActionResolver
{
    public function __construct(
        private readonly DiscoveryDirectoryBackedFamilyManagementActionServiceInterface $actionService,
    ) {
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?DiscoveryDirectoryBackedFamilyManagementActionResultDTO
    {
        $action = $request->query->get('action');

        if (!is_string($action) || '' === $action) {
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

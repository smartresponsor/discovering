<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DirectoryBackedFamilyManagementActionResult;
use Symfony\Component\HttpFoundation\Request;

final class DirectoryBackedFamilyManagementSurfaceActionResolver
{
    public function __construct(
        private readonly DirectoryBackedFamilyManagementActionServiceInterface $actionService,
    ) {
    }

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

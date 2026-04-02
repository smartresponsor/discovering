<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementActionResult;
use Symfony\Component\HttpFoundation\Request;

final class BriefingManagementSurfaceActionResolver
{
    public function __construct(
        private readonly BriefingManagementActionService $actionService,
    ) {
    }

    public function resolve(Request $request): ?BriefingManagementActionResult
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

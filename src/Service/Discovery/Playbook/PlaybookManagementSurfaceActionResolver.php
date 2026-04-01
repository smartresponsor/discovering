<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementActionResult;
use Symfony\Component\HttpFoundation\Request;

final class PlaybookManagementSurfaceActionResolver
{
    public function __construct(
        private readonly PlaybookManagementActionService $actionService,
    ) {
    }

    public function resolve(Request $request): ?PlaybookManagementActionResult
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

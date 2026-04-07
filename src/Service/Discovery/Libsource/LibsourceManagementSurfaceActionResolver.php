<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource;

use App\Dto\Discovery\LibsourceManagementActionResult;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides the libsource management surface action resolver capability within the discovery component.
 */
final class LibsourceManagementSurfaceActionResolver
{
    public function __construct(
        private readonly LibsourceManagementActionService $actionService,
    ) {
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?LibsourceManagementActionResult
    {
        $action = $request->query->get('action');

        if (!is_string($action) || $action === '') {
            return null;
        }

        return match ($action) {
            'audit-alignment' => $this->actionService->auditAlignment(),
            'rebuild-coverage-snapshot' => $this->actionService->rebuildCoverageSnapshot(),
            'clear-event-log' => $this->actionService->clearEventLog(),
            'inspect' => $this->resolveInspect($request),
            default => null,
        };
    }

    private function resolveInspect(Request $request): ?LibsourceManagementActionResult
    {
        $sourceName = $request->query->get('sourceName');

        if (!is_string($sourceName) || $sourceName === '') {
            return null;
        }

        return $this->actionService->inspect($sourceName);
    }
}

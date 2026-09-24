<?php

declare(strict_types=1);

namespace App\Discovering\Resolver\Libsource;

use App\Discovering\DTO\DiscoveryLibsourceManagementActionResultDTO;
use App\Discovering\Service\Libsource\DiscoveryLibsourceManagementActionService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the libsource management surface action resolver capability within the discovery component.
 */
final class DiscoveryLibsourceManagementSurfaceActionResolver
{
    public function __construct(
        private readonly DiscoveryLibsourceManagementActionService $actionService,
    ) {
    }

    /**
     * Performs the resolve operation for this discovery service.
     */
    public function resolve(Request $request): ?DiscoveryLibsourceManagementActionResultDTO
    {
        $action = $request->query->get('action');

        if (!is_string($action) || '' === $action) {
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

    private function resolveInspect(Request $request): ?DiscoveryLibsourceManagementActionResultDTO
    {
        $sourceName = $request->query->get('sourceName');

        if (!is_string($sourceName) || '' === $sourceName) {
            return null;
        }

        return $this->actionService->inspect($sourceName);
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Controller\Management;

use App\Discovering\Dto\Discovery\LibsourceEventLogQuery;
use App\Discovering\Dto\Discovery\LibsourceOperatorEvent;
use App\Discovering\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Service\Discovery\Libsource\LibsourceEventLogSurfaceBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles management HTTP endpoints for the discovery libsource log management surface.
 */
final class DiscoveryLibsourceLogManagementController
{
    public function __construct(
        private readonly LibsourceEventLogSurfaceBuilder $surfaceBuilder,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    /**
     * Handles the index endpoint for the discovery libsource log management HTTP surface.
     */
    #[Route('/management/discovery/libsource/log', name: 'app_management_discovery_libsource_log', methods: ['GET'])]
    /**
     * @return Response|array<string, mixed>
     */
    public function index(Request $request): Response|array
    {
        $query = $this->createQuery($request);

        return [
            '_view' => [
                'surface' => 'discovery',
                'operation' => 'libsource-log',
                'component' => 'Discovering',
                'intent' => 'management',
            ],
            'data' => [
                'surface' => $this->surfaceBuilder->build($query),
            ],
            'meta' => [
                'source_controller' => self::class,
                'legacy_template' => 'management/discovery/libsource_log.html.twig',
            ],
        ];
    }

    /**
     * Handles the export endpoint for the discovery libsource log management HTTP surface.
     */
    #[Route('/management/discovery/libsource/log/export', name: 'app_management_discovery_libsource_log_export', methods: ['GET'])]
    public function export(Request $request): JsonResponse
    {
        $query = $this->createQuery($request);
        $surface = $this->surfaceBuilder->build($query);

        return $this->jsonResponseFactory->success([
            'backendClass' => $surface->backendClass,
            'totalEvents' => $surface->totalEvents,
            'filteredTotalEvents' => $surface->filteredTotalEvents,
            'availablePresets' => $surface->availablePresets,
            'activePreset' => $surface->activePreset,
            'activeLevel' => $surface->activeLevel,
            'activeSearch' => $surface->activeSearch,
            'page' => $surface->page,
            'perPage' => $surface->perPage,
            'totalPages' => $surface->totalPages,
            'events' => array_map(
                static fn (LibsourceOperatorEvent $event): array => [
                    'eventName' => $event->eventName,
                    'level' => $event->level,
                    'summary' => $event->summary,
                    'context' => $event->context,
                ],
                $surface->events,
            ),
        ]);
    }

    private function createQuery(Request $request): LibsourceEventLogQuery
    {
        $preset = $request->query->get('preset');
        $search = $request->query->get('search');
        $level = $request->query->get('level');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 10);

        return new LibsourceEventLogQuery(
            preset: is_string($preset) && '' !== $preset ? $preset : null,
            search: is_string($search) && '' !== $search ? $search : null,
            level: is_string($level) && '' !== $level ? $level : null,
            page: $page > 0 ? $page : 1,
            perPage: $perPage > 0 ? $perPage : 10,
        );
    }
}

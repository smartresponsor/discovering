<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Dto\Discovery\LibsourceEventLogQuery;
use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\LibsourceEventLogSurfaceBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryLibsourceLogManagementController extends AbstractController
{
    public function __construct(
        private readonly LibsourceEventLogSurfaceBuilder $surfaceBuilder,
    ) {
    }

    #[Route('/management/discovery/libsource/log', name: 'app_management_discovery_libsource_log', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = $this->createQuery($request);

        return $this->render('management/discovery/libsource_log.html.twig', [
            'surface' => $this->surfaceBuilder->build($query),
        ]);
    }

    #[Route('/management/discovery/libsource/log/export', name: 'app_management_discovery_libsource_log_export', methods: ['GET'])]
    public function export(Request $request): JsonResponse
    {
        $query = $this->createQuery($request);
        $surface = $this->surfaceBuilder->build($query);

        return $this->json([
            'backendClass' => $surface->backendClass,
            'totalEvents' => $surface->totalEvents,
            'filteredTotalEvents' => $surface->filteredTotalEvents,
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
        $search = $request->query->get('search');
        $level = $request->query->get('level');
        $page = (int) ($request->query->get('page', 1));
        $perPage = (int) ($request->query->get('perPage', 10));

        return new LibsourceEventLogQuery(
            search: is_string($search) && $search !== '' ? $search : null,
            level: is_string($level) && $level !== '' ? $level : null,
            page: $page > 0 ? $page : 1,
            perPage: $perPage > 0 ? $perPage : 10,
        );
    }
}

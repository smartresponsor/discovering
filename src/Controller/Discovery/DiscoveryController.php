<?php
declare(strict_types=1);

namespace App\Controller\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Form\Discovery\DiscoverySearchType;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryController extends AbstractController
{
    public function __construct(private readonly DiscoveryServiceInterface $discoveryService)
    {
    }

    #[Route('/discovery', name: 'app_discovery_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $query = DiscoveryQuery::fromArray([
            'query' => (string) $request->request->get('query', $request->query->get('query', '')),
            'resource' => (string) $request->request->get('resource', $request->query->get('resource', 'global')),
            'limit' => (int) $request->request->get('limit', $request->query->getInt('limit', 20)),
            'offset' => (int) $request->request->get('offset', $request->query->getInt('offset', 0)),
        ]);

        $form = $this->createForm(DiscoverySearchType::class, $query);
        $form->handleRequest($request);
        $result = $this->discoveryService->discover($query);

        return $this->render('discovery/index.html.twig', [
            'form' => $form->createView(),
            'query' => $query,
            'result' => $result,
        ]);
    }

    #[Route('/api/discovery', name: 'app_discovery_api', methods: ['GET'])]
    public function api(Request $request): JsonResponse
    {
        $query = DiscoveryQuery::fromArray([
            'query' => (string) $request->query->get('query', ''),
            'resource' => (string) $request->query->get('resource', 'global'),
            'limit' => $request->query->getInt('limit', 20),
            'offset' => $request->query->getInt('offset', 0),
        ]);

        return $this->json(['ok' => true, 'data' => $this->discoveryService->discover($query)->toArray()]);
    }
}

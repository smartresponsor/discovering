<?php
declare(strict_types=1);

namespace App\Controller\Discovery;

use App\Dto\Discovery\DiscoveryMode;
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
        $query = $this->buildDiscoveryQuery($request, true);

        $form = $this->createForm(DiscoverySearchType::class, $query);
        $form->handleRequest($request);
        $result = $this->discoveryService->discover($query);

        return $this->render('discovery/index.html.twig', [
            'form' => $form->createView(),
            'query' => $result->query,
            'result' => $result,
        ]);
    }

    #[Route('/api/discovery', name: 'app_discovery_api', methods: ['GET'])]
    public function api(Request $request): JsonResponse
    {
        $query = $this->buildDiscoveryQuery($request, false);

        return $this->json(['ok' => true, 'data' => $this->discoveryService->discover($query)->toArray()]);
    }

    private function buildDiscoveryQuery(Request $request, bool $allowFormFallback): DiscoveryQuery
    {
        $source = $allowFormFallback ? $request->request : $request->query;
        $fallback = $request->query;
        $status = $this->stringOrNull($source->get('status', $fallback->get('status')));

        $filters = [];
        if ($status !== null) {
            $filters['status'] = $status;
        }

        return DiscoveryQuery::fromArray([
            'query' => (string) $source->get('query', $fallback->get('query', '')),
            'resource' => (string) $source->get('resource', $fallback->get('resource', 'global')),
            'limit' => (int) $source->get('limit', $fallback->getInt('limit', 20)),
            'offset' => (int) $source->get('offset', $fallback->getInt('offset', 0)),
            'filters' => $filters,
            'resourceWeights' => $this->extractResourceWeights($request),
            'mode' => (string) $source->get('mode', $fallback->get('mode', DiscoveryMode::RELEVANCE)),
        ]);
    }

    /** @return array<string, float> */
    private function extractResourceWeights(Request $request): array
    {
        $weightMap = [
            'global' => 'global_weight',
            'project' => 'project_weight',
            'offering' => 'offering_weight',
            'document' => 'document_weight',
            'playbook' => 'playbook_weight',
            'briefing' => 'briefing_weight',
        ];

        $weights = [];
        foreach ($weightMap as $resource => $parameter) {
            $value = $request->query->get($parameter);
            if (!is_numeric($value)) {
                continue;
            }

            $weights[$resource] = max(0.1, (float) $value);
        }

        return $weights;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}

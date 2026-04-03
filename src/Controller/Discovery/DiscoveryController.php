<?php
declare(strict_types=1);

namespace App\Controller\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;
use App\Form\Discovery\DiscoverySearchType;
use App\Service\Discovery\DiscoveryLearningService;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryServiceInterface $discoveryService,
        private readonly DiscoveryLearningService $learningService,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
    }

    #[Route('/discovery', name: 'app_discovery_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $query = $this->buildDiscoveryQuery($request, true);

        $form = $this->createForm(DiscoverySearchType::class, $query);
        $form->handleRequest($request);
        $result = $this->discoveryService->discover($query);
        $this->operationLogger->recordHttp('discovery.ui.query', context: [
            'resource' => $result->query->resource,
            'mode' => $result->query->mode,
            'query' => $result->query->query,
            'total' => $result->total,
        ]);

        return $this->render('discovery/index.html.twig', [
            'form' => $form->createView(),
            'query' => $result->query,
            'result' => $result,
        ]);
    }

    #[Route('/discovery/feedback', name: 'app_discovery_feedback', methods: ['POST'])]
    public function feedback(Request $request): RedirectResponse
    {
        $count = $this->learningService->recordUsefulClick(
            resource: (string) $request->request->get('resource', 'global'),
            hitId: (string) $request->request->get('id', ''),
            title: (string) $request->request->get('title', ''),
            reference: (string) $request->request->get('reference', ''),
        );

        $this->operationLogger->recordHttp('discovery.ui.feedback', context: [
            'resource' => (string) $request->request->get('resource', 'global'),
            'id' => (string) $request->request->get('id', ''),
            'feedbackCount' => $count,
        ]);

        $this->addFlash('success', sprintf('Recorded useful click (%d total).', $count));

        $returnTo = (string) $request->request->get('return_to', $this->generateUrl('app_discovery_index'));

        return $this->redirect($returnTo);
    }

    #[Route('/api/discovery', name: 'app_discovery_api', methods: ['GET'])]
    public function api(Request $request): JsonResponse
    {
        $query = $this->buildDiscoveryQuery($request, false);
        $result = $this->discoveryService->discover($query);
        $this->operationLogger->recordHttp('discovery.api.query', context: [
            'resource' => $result->query->resource,
            'mode' => $result->query->mode,
            'query' => $result->query->query,
            'total' => $result->total,
        ]);

        return $this->json(['ok' => true, 'data' => $result->toArray()]);
    }

    #[Route('/api/discovery/click', name: 'app_discovery_api_click', methods: ['POST'])]
    public function click(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $count = $this->learningService->recordUsefulClick(
            resource: (string) ($payload['resource'] ?? 'global'),
            hitId: (string) ($payload['id'] ?? ''),
            title: (string) ($payload['title'] ?? ''),
            reference: (string) ($payload['reference'] ?? ''),
        );

        $this->operationLogger->recordHttp('discovery.api.click', context: [
            'resource' => (string) ($payload['resource'] ?? 'global'),
            'id' => (string) ($payload['id'] ?? ''),
            'feedbackCount' => $count,
        ]);

        return $this->json([
            'ok' => true,
            'data' => [
                'resource' => (string) ($payload['resource'] ?? 'global'),
                'id' => (string) ($payload['id'] ?? ''),
                'feedbackCount' => $count,
            ],
        ]);
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

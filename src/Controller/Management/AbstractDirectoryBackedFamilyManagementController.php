<?php

declare(strict_types=1);

namespace App\Discovering\Controller\Management;

use App\Discovering\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Handles management HTTP endpoints for the abstract directory backed family management surface.
 */
abstract class AbstractDirectoryBackedFamilyManagementController
{
    public function __construct(private readonly DiscoveryJsonResponseFactory $jsonResponseFactory)
    {
    }

    /**
     * @param list<object> $operatorEventTrail
     */
    /**
     * @return array<string, mixed>
     */
    protected function renderDirectoryBackedFamilyManagement(
        string $template,
        object $surface,
        mixed $lastActionResult,
        array $operatorEventTrail,
    ): array {
        $operation = basename($template, '.html.twig');
        $operation = str_replace('_', '-', $operation);

        return [
            '_view' => [
                'surface' => 'discovery',
                'operation' => $operation,
                'component' => 'Discovering',
                'intent' => 'management',
            ],
            'data' => [
                'surface' => $surface,
                'lastActionResult' => $lastActionResult,
                'operatorEventTrail' => $operatorEventTrail,
            ],
            'meta' => [
                'source_controller' => static::class,
                'legacy_template' => $template,
            ],
        ];
    }

    /**
     * Handles the exportDirectoryBackedFamilySource endpoint for the abstract directory backed family management HTTP surface.
     */
    protected function exportDirectoryBackedFamilySource(
        AbstractDirectoryBackedDiscoverySourceRecordRepository $repository,
    ): JsonResponse {
        return $this->jsonResponseFactory->success([
            'sourceName' => $repository->getSourceName(),
            'storagePath' => $repository->getStoragePath(),
            'records' => json_decode($repository->exportJson(), true),
        ]);
    }
}

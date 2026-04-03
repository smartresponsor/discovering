<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractDirectoryBackedFamilyManagementController extends AbstractController
{
    public function __construct(private readonly DiscoveryJsonResponseFactory $jsonResponseFactory)
    {
    }

    /**
     * @param list<object> $operatorEventTrail
     */
    protected function renderDirectoryBackedFamilyManagement(
        string $template,
        object $surface,
        mixed $lastActionResult,
        array $operatorEventTrail,
    ): Response {
        return $this->render($template, [
            'surface' => $surface,
            'lastActionResult' => $lastActionResult,
            'operatorEventTrail' => $operatorEventTrail,
        ]);
    }

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

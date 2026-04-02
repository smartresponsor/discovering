<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use App\Service\Discovery\Libsource\LibsourceManagementSurfaceActionResolver;
use App\Service\Discovery\Libsource\LibsourceOperatorEventTrailBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryLibsourceManagementController extends AbstractController
{
    public function __construct(
        private readonly LibsourceDiagnosticSurfaceBuilder $diagnosticSurfaceBuilder,
        private readonly LibsourceManagementSurfaceActionResolver $actionResolver,
        private readonly LibsourceOperatorEventTrailBuilder $eventTrailBuilder,
    ) {
    }

    #[Route('/management/discovery/libsource', name: 'app_management_discovery_libsource', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $lastActionResult = $this->actionResolver->resolve($request);

        return $this->render('management/discovery/libsource.html.twig', [
            'surface' => $this->diagnosticSurfaceBuilder->build(),
            'lastActionResult' => $lastActionResult,
            'operatorEventTrail' => $this->eventTrailBuilder->build($lastActionResult),
        ]);
    }
}

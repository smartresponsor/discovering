<?php

declare(strict_types=1);

namespace App\Controller\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Form\Discovery\DiscoverySearchType;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DiscoveryController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryServiceInterface $discoveryService,
    ) {
    }

    public function index(Request $request): Response
    {
        $query = new DiscoveryQuery();
        $form = $this->createForm(DiscoverySearchType::class, $query);
        $form->handleRequest($request);

        $result = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->discoveryService->discover($query);
        }

        return $this->render('discovery/index.html.twig', [
            'form' => $form->createView(),
            'result' => $result,
        ]);
    }
}

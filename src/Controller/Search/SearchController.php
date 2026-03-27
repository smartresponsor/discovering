<?php
declare(strict_types=1);

namespace App\Controller\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; use Symfony\Component\HttpFoundation\JsonResponse;
use App\SearchInterface\Adapter\SearchAdapterInterface;
final class SearchController extends AbstractController {
    public function __construct(private SearchAdapterInterface $adapter) {}
    #[Route('/api/project:search', methods:['GET'])]
    public function search(Request $r): JsonResponse {
        $q=(string)$r->query->get('q',''); $limit=(int)$r->query->get('limit',20); $offset=(int)$r->query->get('offset',0);
        $rows=$this->adapter->search('project', $q, $limit, $offset);
        return $this->json(['ok'=>true,'data'=>$rows]);
    }
    #[Route('/api/search:rebuild', methods:['POST'])]
    public function rebuild(): JsonResponse { $this->adapter->createIndex('project'); return $this->json(['ok'=>true]); }
}

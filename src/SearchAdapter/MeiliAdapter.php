<?php
declare(strict_types=1);

namespace App\SearchAdapter;
use App\SearchInterface\Adapter\SearchAdapterInterface;
final class MeiliAdapter implements SearchAdapterInterface {
    private string $base; private ?string $key;
    public function __construct(?string $base=null, ?string $key=null) {
        $this->base = rtrim($base ?? (getenv('SEARCH_URL') ?: ''), '/');
        $this->key  = $key ?? (getenv('SEARCH_API_KEY') ?: null);
    }
    private function req(string $method, string $path, ?array $body=null): array {
        $opts = ['http'=>['method'=>$method,'header'=>["Content-Type: application/json"], 'ignore_errors'=>true]];
        if ($this->key) { $opts['http']['header'][] = 'Authorization: Bearer '.$this->key; }
        if ($body !== null) $opts['http']['content'] = json_encode($body, JSON_UNESCAPED_UNICODE);
        $res = file_get_contents($this->base.$path, false, stream_context_create($opts));
        return json_decode($res ?: "[]", true) ?: [];
    }
    public function createIndex(string $index): void { if($this->base!=='') $this->req('POST', '/indexes', ['uid'=>$index]); }
    public function upsert(string $index, string $id, array $doc): void { $doc['id']=$id; if($this->base!=='') $this->req('POST', "/indexes/$index/documents", [$doc]); }
    public function remove(string $index, string $id): void { if($this->base!=='') $this->req('DELETE', "/indexes/$index/documents/$id"); }
    public function search(string $index, string $query, int $limit=20, int $offset=0): array {
        if($this->base==='') return [];
        $r = $this->req('POST', "/indexes/$index/search", ['q'=>$query, 'limit'=>$limit, 'offset'=>$offset]);
        return $r['hits'] ?? [];
    }
    public function aliasSwap(string $from, string $to): void {}
}

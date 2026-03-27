<?php
declare(strict_types=1);

namespace App\SearchAdapter;
use App\SearchInterface\Adapter\SearchAdapterInterface;
final class SqliteFtsAdapter implements SearchAdapterInterface {
    private \PDO $pdo;
    public function __construct(?string $path=null) {
        $db = $path ?? (getenv('SEARCH_SQLITE_PATH') ?: '/data/search/index.sqlite');
        $dir = dirname($db); if (!is_dir($dir)) { @mkdir($dir, 0o777, true); }
        $this->pdo = new \PDO('sqlite:'.$db);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    public function createIndex(string $index): void {
        $this->pdo->exec("CREATE VIRTUAL TABLE IF NOT EXISTS {$index} USING fts5(id UNINDEXED, name, metaCode, status, content)");
    }
    public function upsert(string $index, string $id, array $doc): void {
        $this->createIndex($index);
        $stmt = $this->pdo->prepare("INSERT INTO {$index}(rowid,id,name,metaCode,status,content) VALUES ((SELECT rowid FROM {$index} WHERE id=?),?,?,?,?,?)");
        $content = ($doc['name']??'').' '+($doc['metaCode']??'').' '+($doc['status']??'');
        $stmt->execute([$id,$id,(string)($doc['name']??''),(string)($doc['metaCode']??''),(string)($doc['status']??''),$content]);
    }
    public function remove(string $index, string $id): void { $this->pdo->prepare("DELETE FROM {$index} WHERE id=?")->execute([$id]); }
    public function search(string $index, string $query, int $limit=20, int $offset=0): array {
        $this->createIndex($index);
        $stmt = $this->pdo->prepare("SELECT id, name, metaCode, status FROM {$index} WHERE {$index} MATCH ? LIMIT ? OFFSET ?");
        $stmt->execute([$query, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
    public function aliasSwap(string $from, string $to): void {}
}

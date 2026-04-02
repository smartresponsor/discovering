<?php
declare(strict_types=1);

namespace App\Service\Discovery\Adapter;

use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use PDO;

final class SqliteFtsDiscoveryAdapter implements DiscoveryAdapterInterface
{
    private PDO $pdo;

    public function __construct(?string $path = null)
    {
        $databasePath = $path ?: (getenv('DISCOVERY_SQLITE_PATH') ?: sys_get_temp_dir() . '/discovering.sqlite');
        $directory = dirname($databasePath);

        if (!is_dir($directory)) {
            @mkdir($directory, 0o777, true);
        }

        $this->pdo = new PDO('sqlite:' . $databasePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function upsert(string $resource, string $id, array $document): void
    {
        $index = $this->normalizeIndexName($resource);
        $this->createIndex($index);
        $this->remove($index, $id);

        $statement = $this->pdo->prepare(sprintf(
            'INSERT INTO %s (id, title, resource, reference, status, content) VALUES (:id, :title, :resource, :reference, :status, :content)',
            $index,
        ));

        $statement->execute([
            'id' => $id,
            'title' => (string) ($document['title'] ?? ''),
            'resource' => (string) ($document['resource'] ?? $resource),
            'reference' => (string) ($document['reference'] ?? ''),
            'status' => (string) ($document['status'] ?? ''),
            'content' => trim(implode(' ', [
                (string) ($document['title'] ?? ''),
                (string) ($document['reference'] ?? ''),
                (string) ($document['status'] ?? ''),
                (string) ($document['content'] ?? ''),
            ])),
        ]);
    }

    public function remove(string $resource, string $id): void
    {
        $index = $this->normalizeIndexName($resource);
        $this->createIndex($index);
        $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE id = :id', $index));
        $statement->execute(['id' => $id]);
    }

    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        $index = $this->normalizeIndexName($resource);
        $this->createIndex($index);

        if ($query === '') {
            $statement = $this->pdo->prepare(sprintf('SELECT id, title, resource, reference, status, NULL AS ftsScore FROM %s ORDER BY rowid DESC LIMIT :limit OFFSET :offset', $index));
            $statement->bindValue('limit', $limit, PDO::PARAM_INT);
            $statement->bindValue('offset', $offset, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $statement = $this->pdo->prepare(sprintf(
            'SELECT id, title, resource, reference, status, bm25(%1$s, 5.0, 1.0, 1.0, 1.0, 0.5) AS ftsScore FROM %1$s WHERE %1$s MATCH :query ORDER BY ftsScore ASC LIMIT :limit OFFSET :offset',
            $index,
        ));
        $statement->bindValue('query', $query);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createIndex(string $resource): void
    {
        $index = $this->normalizeIndexName($resource);
        $this->pdo->exec(sprintf('CREATE VIRTUAL TABLE IF NOT EXISTS %s USING fts5(id UNINDEXED, title, resource, reference, status, content)', $index));
    }

    public function swapAlias(string $from, string $to): void
    {
    }

    private function normalizeIndexName(string $resource): string
    {
        $normalized = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($resource)) ?: 'global';
        return trim($normalized, '_') ?: 'global';
    }
}

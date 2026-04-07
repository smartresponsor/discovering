<?php
declare(strict_types=1);

namespace App\Service\Discovery\Adapter;

use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableAdapterInterface;
use PDO;


/**
 * Implements the sqlite fts discovery adapter used by the discovery runtime.
 */
final class SqliteFtsDiscoveryAdapter implements DiscoveryAdapterInterface, DiscoveryStagingCapableAdapterInterface
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly ?string $path = null,
    ) {
    }

    /**
     * Performs the upsert operation for this discovery service.
     */
    public function upsert(string $resource, string $id, array $document): void
    {
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));
        $this->createIndex($index);
        $this->remove($index, $id);

        $statement = $this->pdo()->prepare(sprintf(
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

    /**
     * Performs the remove operation for this discovery service.
     */
    public function remove(string $resource, string $id): void
    {
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));
        $this->createIndex($index);
        $statement = $this->pdo()->prepare(sprintf('DELETE FROM %s WHERE id = :id', $index));
        $statement->execute(['id' => $id]);
    }

    /**
     * Executes the search workflow against the active discovery source or backend.
     */
    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));
        $this->createIndex($index);

        if ($query === '') {
            $statement = $this->pdo()->prepare(sprintf('SELECT id, title, resource, reference, status, content, NULL AS ftsScore FROM %s ORDER BY rowid DESC LIMIT :limit OFFSET :offset', $index));
            $statement->bindValue('limit', $limit, PDO::PARAM_INT);
            $statement->bindValue('offset', $offset, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $statement = $this->pdo()->prepare(sprintf(
            'SELECT id, title, resource, reference, status, content, bm25(%1$s, 5.0, 1.0, 1.0, 1.0, 0.5) AS ftsScore FROM %1$s WHERE %1$s MATCH :query ORDER BY ftsScore ASC LIMIT :limit OFFSET :offset',
            $index,
        ));
        $statement->bindValue('query', $query);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Performs the create index operation for this discovery service.
     */
    public function createIndex(string $resource): void
    {
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));
        $this->pdo()->exec(sprintf('CREATE VIRTUAL TABLE IF NOT EXISTS %s USING fts5(id UNINDEXED, title, resource, reference, status, content)', $index));
    }

    /**
     * Performs the swap alias operation for this discovery service.
     */
    public function swapAlias(string $from, string $to): void
    {
        $alias = $this->normalizeIndexName($from);
        $target = $this->normalizeIndexName($to);
        $this->createIndex($target);
        $statement = $this->pdo()->prepare('INSERT INTO discovery_index_aliases (alias, target) VALUES (:alias, :target) ON CONFLICT(alias) DO UPDATE SET target = excluded.target');
        $statement->execute([
            'alias' => $alias,
            'target' => $target,
        ]);
    }

    /**
     * Returns the backend name value exposed by this service.
     */
    public function getBackendName(): string
    {
        return 'sqlite-fts5';
    }

    /**
     * Performs the supports staged rebuild operation for this discovery service.
     */
    public function supportsStagedRebuild(): bool
    {
        return true;
    }

    private function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $databasePath = $this->path ?: (getenv('DISCOVERY_SQLITE_PATH') ?: sys_get_temp_dir() . '/discovering.sqlite');
        $directory = dirname($databasePath);

        if (!is_dir($directory)) {
            @mkdir($directory, 0o777, true);
        }

        $pdo = new PDO('sqlite:' . $databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
        $this->createAliasTable();

        return $this->pdo;
    }

    private function createAliasTable(): void
    {
        $this->pdo()->exec('CREATE TABLE IF NOT EXISTS discovery_index_aliases (alias TEXT PRIMARY KEY, target TEXT NOT NULL)');
    }

    private function resolveActiveIndex(string $resource): string
    {
        $resolved = $this->normalizeIndexName($resource);

        for ($i = 0; $i < 8; ++$i) {
            $statement = $this->pdo()->prepare('SELECT target FROM discovery_index_aliases WHERE alias = :alias LIMIT 1');
            $statement->execute(['alias' => $resolved]);
            $target = $statement->fetchColumn();
            if (!is_string($target) || $target === '' || $target === $resolved) {
                return $resolved;
            }

            $resolved = $this->normalizeIndexName($target);
        }

        return $resolved;
    }

    private function normalizeIndexName(string $resource): string
    {
        $normalized = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($resource)) ?: 'global';
        return trim($normalized, '_') ?: 'global';
    }
}

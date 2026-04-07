<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

use App\Dto\Discovery\DiscoveryRebuildSummary;
use PDO;
use PDOException;


/**
 * Provides the pdo discovery rebuild evidence store capability within the discovery component.
 */
final class PdoDiscoveryRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    private ?PDO $pdo = null;
    private bool $schemaReady = false;

    public function __construct(
        private readonly string $dsn,
        private readonly ?string $user,
        private readonly ?string $password,
        private readonly string $tableName,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryRebuildSummary $summary): void
    {
        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->prepare(sprintf(
            'INSERT INTO %s (evidence_id, resource, finished_at, payload_json) VALUES (:evidence_id, :resource, :finished_at, :payload_json)',
            $this->quotedTableName(),
        ));

        $statement->execute([
            'evidence_id' => $summary->evidenceId,
            'resource' => $summary->resource,
            'finished_at' => $summary->finishedAt,
            'payload_json' => json_encode($summary->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Performs the latest operation for this discovery service.
     */
    public function latest(int $limit = 20): array
    {
        if ($limit <= 0) {
            return [];
        }

        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->prepare(sprintf(
            'SELECT payload_json FROM %s ORDER BY finished_at DESC, evidence_id DESC LIMIT :limit',
            $this->quotedTableName(),
        ));
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return [];
        }

        $summaries = [];
        foreach ($rows as $row) {
            $payload = (string) ($row['payload_json'] ?? '');
            if ($payload === '') {
                continue;
            }
            foreach ($this->serializer()->decode(sprintf('[%s]', $payload)) as $summary) {
                $summaries[] = $summary;
            }
        }

        return $summaries;
    }

    private function serializer(): DiscoveryRebuildEvidenceJsonSerializer
    {
        return new DiscoveryRebuildEvidenceJsonSerializer();
    }

    private function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (trim($this->dsn) === '') {
            throw new \RuntimeException('Discovery PDO rebuild evidence backend requires APP_DISCOVERY_REBUILD_EVIDENCE_PDO_DSN to be configured.');
        }

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new \RuntimeException('Unable to connect discovery PDO rebuild evidence backend.', 0, $exception);
        }

        return $this->pdo;
    }

    private function ensureSchema(PDO $pdo): void
    {
        if ($this->schemaReady) {
            return;
        }

        $pdo->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (evidence_id VARCHAR(128) PRIMARY KEY, resource VARCHAR(64) NOT NULL, finished_at VARCHAR(64) NOT NULL, payload_json TEXT NOT NULL)',
            $this->quotedTableName(),
        ));
        $this->schemaReady = true;
    }

    private function quotedTableName(): string
    {
        $table = trim($this->tableName);
        if ($table === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \RuntimeException(sprintf('Invalid discovery rebuild evidence table name "%s".', $this->tableName));
        }

        return $table;
    }
}

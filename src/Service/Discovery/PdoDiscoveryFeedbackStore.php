<?php

declare(strict_types=1);

namespace App\Service\Discovery;

use PDO;
use PDOException;

/**
 * Shared feedback store backed by a PDO coordination table.
 * 
 * This allows useful-click learning signals to coordinate across replicas when
 * a shared RDBMS is configured instead of a local SQLite file.
 */
/**
 * Provides the pdo discovery feedback store capability within the discovery component.
 */
final class PdoDiscoveryFeedbackStore implements DiscoveryFeedbackStoreInterface
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
     * Records the click signal for discovery state and analytics flows.
     */
    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        if (trim($this->dsn) === '') {
            throw new \RuntimeException('Discovery PDO feedback backend requires APP_DISCOVERY_FEEDBACK_PDO_DSN to be configured.');
        }

        $pdo = $this->pdo();
        $table = $this->quotedTableName();
        $lastClickedAt = gmdate(DATE_ATOM);

        for ($attempt = 0; $attempt < 5; ++$attempt) {
            try {
                $pdo->beginTransaction();
                $this->ensureSchema($pdo, $table);

                $select = $pdo->prepare(sprintf('SELECT click_count FROM %s WHERE resource = :resource AND hit_id = :hit_id', $table));
                $select->execute([
                    'resource' => $resource,
                    'hit_id' => $hitId,
                ]);
                $row = $select->fetch(PDO::FETCH_ASSOC);

                if ($row === false) {
                    try {
                        $insert = $pdo->prepare(sprintf('INSERT INTO %s (resource, hit_id, title, reference, click_count, last_clicked_at) VALUES (:resource, :hit_id, :title, :reference, :click_count, :last_clicked_at)', $table));
                        $insert->execute([
                            'resource' => $resource,
                            'hit_id' => $hitId,
                            'title' => $title,
                            'reference' => $reference,
                            'click_count' => 1,
                            'last_clicked_at' => $lastClickedAt,
                        ]);
                        $pdo->commit();

                        return 1;
                    } catch (PDOException $exception) {
                        $this->rollbackQuietly($pdo);
                        if ($this->isRetryableWriteConflict($exception)) {
                            continue;
                        }

                        throw $exception;
                    }
                }

                $oldCount = (int) ($row['click_count'] ?? 0);
                $newCount = $oldCount + 1;
                $update = $pdo->prepare(sprintf(
                    'UPDATE %s SET title = :title, reference = :reference, click_count = :new_count, last_clicked_at = :last_clicked_at WHERE resource = :resource AND hit_id = :hit_id AND click_count = :old_count',
                    $table,
                ));
                $update->execute([
                    'title' => $title,
                    'reference' => $reference,
                    'new_count' => $newCount,
                    'last_clicked_at' => $lastClickedAt,
                    'resource' => $resource,
                    'hit_id' => $hitId,
                    'old_count' => $oldCount,
                ]);

                if ($update->rowCount() !== 1) {
                    $this->rollbackQuietly($pdo);
                    continue;
                }

                $pdo->commit();

                return $newCount;
            } catch (PDOException $exception) {
                $this->rollbackQuietly($pdo);
                if ($this->isRetryableWriteConflict($exception)) {
                    continue;
                }

                throw new \RuntimeException('Discovery PDO feedback store failed while updating the coordination table.', 0, $exception);
            }
        }

        throw new \RuntimeException('Discovery PDO feedback store exhausted retry attempts while updating the coordination table.');
    }

    /**
     * Returns the click count value exposed by this service.
     */
    public function getClickCount(string $resource, string $hitId): int
    {
        if (trim($this->dsn) === '') {
            return 0;
        }

        $pdo = $this->pdo();
        $table = $this->quotedTableName();
        $this->ensureSchema($pdo, $table);

        $statement = $pdo->prepare(sprintf('SELECT click_count FROM %s WHERE resource = :resource AND hit_id = :hit_id', $table));
        $statement->execute([
            'resource' => $resource,
            'hit_id' => $hitId,
        ]);

        $count = $statement->fetchColumn();

        return $count === false ? 0 : max(0, (int) $count);
    }

    private function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new \RuntimeException('Unable to connect discovery PDO feedback backend.', 0, $exception);
        }

        return $this->pdo;
    }

    private function ensureSchema(PDO $pdo, string $table): void
    {
        if ($this->schemaReady) {
            return;
        }

        $pdo->exec(sprintf("CREATE TABLE IF NOT EXISTS %s (resource VARCHAR(64) NOT NULL, hit_id VARCHAR(255) NOT NULL, title TEXT NOT NULL DEFAULT '', reference TEXT NOT NULL DEFAULT '', click_count INTEGER NOT NULL DEFAULT 0, last_clicked_at VARCHAR(64) DEFAULT NULL, PRIMARY KEY(resource, hit_id))", $table));
        $this->schemaReady = true;
    }

    private function quotedTableName(): string
    {
        $table = trim($this->tableName);
        if ($table === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \RuntimeException(sprintf('Invalid discovery feedback table name "%s".', $this->tableName));
        }

        return $table;
    }

    private function rollbackQuietly(PDO $pdo): void
    {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    private function isRetryableWriteConflict(PDOException $exception): bool
    {
        $sqlState = (string) $exception->getCode();

        return in_array($sqlState, ['23000', '23505', '40001'], true);
    }
}

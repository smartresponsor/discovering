<?php

declare(strict_types=1);

namespace App\Service\Discovery\RateLimit;

use PDO;
use PDOException;

/**
 * Database-backed fixed-window rate limit store.
 * 
 * This store is intended for stronger shared coordination than local JSON files.
 * It uses optimistic retries so a shared RDBMS can coordinate counters across
 * multiple application replicas.
 */
/**
 * Provides the pdo discovery rate limit store capability within the discovery component.
 */
final class PdoDiscoveryRateLimitStore implements DiscoveryRateLimitStoreInterface
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
     * Performs the increment operation for this discovery service.
     */
    public function increment(string $scope, string $actorKey, int $windowSeconds): array
    {
        if (trim($this->dsn) === '') {
            throw new \RuntimeException('Discovery PDO rate limit backend requires APP_DISCOVERY_RATE_LIMIT_PDO_DSN to be configured.');
        }

        $bucket = $scope . '|' . $actorKey;
        $windowSeconds = max(1, $windowSeconds);
        $pdo = $this->pdo();
        $table = $this->quotedTableName();

        for ($attempt = 0; $attempt < 5; ++$attempt) {
            $now = time();

            try {
                $pdo->beginTransaction();
                $this->deleteExpired($pdo, $table, $now);

                $select = $pdo->prepare(sprintf('SELECT bucket_count, reset_at FROM %s WHERE bucket = :bucket', $table));
                $select->execute(['bucket' => $bucket]);
                $row = $select->fetch(PDO::FETCH_ASSOC);

                if ($row === false) {
                    $resetAt = $now + $windowSeconds;
                    $count = 1;

                    try {
                        $insert = $pdo->prepare(sprintf('INSERT INTO %s (bucket, bucket_count, reset_at) VALUES (:bucket, :bucket_count, :reset_at)', $table));
                        $insert->execute([
                            'bucket' => $bucket,
                            'bucket_count' => $count,
                            'reset_at' => $resetAt,
                        ]);
                        $pdo->commit();

                        return ['count' => $count, 'resetAt' => $resetAt];
                    } catch (PDOException $exception) {
                        $this->rollbackQuietly($pdo);
                        if ($this->isRetryableWriteConflict($exception)) {
                            continue;
                        }

                        throw $exception;
                    }
                }

                $existingCount = (int) ($row['bucket_count'] ?? 0);
                $existingResetAt = (int) ($row['reset_at'] ?? 0);
                if ($existingResetAt <= $now) {
                    $existingCount = 0;
                    $existingResetAt = $now + $windowSeconds;
                }

                $newCount = $existingCount + 1;
                $newResetAt = $existingResetAt;

                $update = $pdo->prepare(sprintf(
                    'UPDATE %s SET bucket_count = :new_count, reset_at = :new_reset_at WHERE bucket = :bucket AND bucket_count = :old_count AND reset_at = :old_reset_at',
                    $table,
                ));
                $update->execute([
                    'new_count' => $newCount,
                    'new_reset_at' => $newResetAt,
                    'bucket' => $bucket,
                    'old_count' => $row['bucket_count'],
                    'old_reset_at' => $row['reset_at'],
                ]);

                if ($update->rowCount() !== 1) {
                    $this->rollbackQuietly($pdo);
                    continue;
                }

                $pdo->commit();

                return ['count' => $newCount, 'resetAt' => $newResetAt];
            } catch (PDOException $exception) {
                $this->rollbackQuietly($pdo);

                if ($this->isRetryableWriteConflict($exception)) {
                    continue;
                }

                throw new \RuntimeException('Discovery PDO rate limit store failed while updating the coordination table.', 0, $exception);
            }
        }

        throw new \RuntimeException('Discovery PDO rate limit store exhausted retry attempts while updating the coordination table.');
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
            throw new \RuntimeException('Unable to connect discovery PDO rate limit backend.', 0, $exception);
        }

        return $this->pdo;
    }

    private function ensureSchema(PDO $pdo): void
    {
        if ($this->schemaReady) {
            return;
        }

        $table = $this->quotedTableName();
        $pdo->exec(sprintf('CREATE TABLE IF NOT EXISTS %s (bucket VARCHAR(255) PRIMARY KEY, bucket_count INTEGER NOT NULL, reset_at BIGINT NOT NULL)', $table));
        $this->schemaReady = true;
    }

    private function deleteExpired(PDO $pdo, string $table, int $now): void
    {
        $this->ensureSchema($pdo);
        $delete = $pdo->prepare(sprintf('DELETE FROM %s WHERE reset_at <= :now', $table));
        $delete->execute(['now' => $now]);
    }

    private function quotedTableName(): string
    {
        $table = trim($this->tableName);
        if ($table === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \RuntimeException(sprintf('Invalid discovery rate limit table name "%s".', $this->tableName));
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

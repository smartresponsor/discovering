<?php

declare(strict_types=1);

namespace App\Service\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;
use PDO;
use PDOException;

final class PdoDiscoveryOperationEventLogStore implements DiscoveryOperationEventLogStoreInterface
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

    public function append(DiscoveryOperationEvent $event): void
    {
        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->prepare(sprintf(
            'INSERT INTO %s (event_id, request_id, channel, operation, status, occurred_at, context_json) VALUES (:event_id, :request_id, :channel, :operation, :status, :occurred_at, :context_json)',
            $this->quotedTableName(),
        ));

        $statement->execute([
            'event_id' => $this->eventId($event),
            'request_id' => $event->requestId,
            'channel' => $event->channel,
            'operation' => $event->operation,
            'status' => $event->status,
            'occurred_at' => $event->occurredAt,
            'context_json' => json_encode($event->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    public function all(): array
    {
        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->query(sprintf(
            'SELECT request_id, channel, operation, status, occurred_at, context_json FROM %s ORDER BY occurred_at ASC, event_id ASC',
            $this->quotedTableName(),
        ));

        if ($statement === false) {
            return [];
        }

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows)) {
            return [];
        }

        return array_map(fn (array $row): DiscoveryOperationEvent => $this->hydrate($row), $rows);
    }

    public function latest(int $limit = 25): array
    {
        if ($limit <= 0) {
            return [];
        }

        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->prepare(sprintf(
            'SELECT request_id, channel, operation, status, occurred_at, context_json FROM %s ORDER BY occurred_at DESC, event_id DESC LIMIT :limit',
            $this->quotedTableName(),
        ));
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return [];
        }

        return array_map(fn (array $row): DiscoveryOperationEvent => $this->hydrate($row), $rows);
    }

    public function clear(): void
    {
        $pdo = $this->pdo();
        $this->ensureSchema($pdo);
        $pdo->exec(sprintf('DELETE FROM %s', $this->quotedTableName()));
    }

    private function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (trim($this->dsn) === '') {
            throw new \RuntimeException('Discovery PDO operation log backend requires APP_DISCOVERY_OPERATION_LOG_PDO_DSN to be configured.');
        }

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new \RuntimeException('Unable to connect discovery PDO operation log backend.', 0, $exception);
        }

        return $this->pdo;
    }

    private function ensureSchema(PDO $pdo): void
    {
        if ($this->schemaReady) {
            return;
        }

        $pdo->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (event_id VARCHAR(128) PRIMARY KEY, request_id VARCHAR(128) NOT NULL, channel VARCHAR(64) NOT NULL, operation VARCHAR(191) NOT NULL, status VARCHAR(32) NOT NULL, occurred_at VARCHAR(64) NOT NULL, context_json TEXT NOT NULL)',
            $this->quotedTableName(),
        ));
        $this->schemaReady = true;
    }

    private function quotedTableName(): string
    {
        $table = trim($this->tableName);
        if ($table === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \RuntimeException(sprintf('Invalid discovery operation log table name "%s".', $this->tableName));
        }

        return $table;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): DiscoveryOperationEvent
    {
        $context = json_decode((string) ($row['context_json'] ?? '{}'), true);

        return new DiscoveryOperationEvent(
            requestId: (string) ($row['request_id'] ?? ''),
            channel: (string) ($row['channel'] ?? 'http'),
            operation: (string) ($row['operation'] ?? ''),
            status: (string) ($row['status'] ?? 'ok'),
            occurredAt: (string) ($row['occurred_at'] ?? gmdate(DATE_ATOM)),
            context: is_array($context) ? $context : [],
        );
    }

    private function eventId(DiscoveryOperationEvent $event): string
    {
        return substr(sha1($event->requestId . '|' . $event->operation . '|' . $event->occurredAt . '|' . json_encode($event->context)), 0, 40);
    }
}

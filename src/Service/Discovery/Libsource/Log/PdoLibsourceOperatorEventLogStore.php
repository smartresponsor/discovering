<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;
use PDO;
use PDOException;

final class PdoLibsourceOperatorEventLogStore implements LibsourceOperatorEventLogStoreInterface
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

    public function append(LibsourceOperatorEvent $event): void
    {
        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->prepare(sprintf(
            'INSERT INTO %s (event_id, event_name, level, summary, context_json) VALUES (:event_id, :event_name, :level, :summary, :context_json)',
            $this->quotedTableName(),
        ));

        $statement->execute([
            'event_id' => $this->eventId($event),
            'event_name' => $event->eventName,
            'level' => $event->level,
            'summary' => $event->summary,
            'context_json' => json_encode($event->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    public function all(): array
    {
        $pdo = $this->pdo();
        $this->ensureSchema($pdo);

        $statement = $pdo->query(sprintf(
            'SELECT event_name, level, summary, context_json FROM %s ORDER BY event_id ASC',
            $this->quotedTableName(),
        ));

        if ($statement === false) {
            return [];
        }

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows)) {
            return [];
        }

        return array_map(fn (array $row): LibsourceOperatorEvent => $this->hydrate($row), $rows);
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
            throw new \RuntimeException('Discovery PDO libsource event backend requires APP_DISCOVERY_LIBSOURCE_EVENT_LOG_PDO_DSN to be configured.');
        }

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new \RuntimeException('Unable to connect discovery PDO libsource event backend.', 0, $exception);
        }

        return $this->pdo;
    }

    private function ensureSchema(PDO $pdo): void
    {
        if ($this->schemaReady) {
            return;
        }

        $pdo->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (event_id VARCHAR(128) PRIMARY KEY, event_name VARCHAR(128) NOT NULL, level VARCHAR(32) NOT NULL, summary TEXT NOT NULL, context_json TEXT NOT NULL)',
            $this->quotedTableName(),
        ));
        $this->schemaReady = true;
    }

    private function quotedTableName(): string
    {
        $table = trim($this->tableName);
        if ($table === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \RuntimeException(sprintf('Invalid libsource event log table name "%s".', $this->tableName));
        }

        return $table;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): LibsourceOperatorEvent
    {
        $context = json_decode((string) ($row['context_json'] ?? '{}'), true);

        return new LibsourceOperatorEvent(
            eventName: (string) ($row['event_name'] ?? ''),
            level: (string) ($row['level'] ?? 'info'),
            summary: (string) ($row['summary'] ?? ''),
            context: is_array($context) ? $context : [],
        );
    }

    private function eventId(LibsourceOperatorEvent $event): string
    {
        return substr(sha1($event->eventName . '|' . $event->level . '|' . $event->summary . '|' . json_encode($event->context)), 0, 40);
    }
}

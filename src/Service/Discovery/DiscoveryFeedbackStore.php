<?php
declare(strict_types=1);

namespace App\Service\Discovery;

use PDO;

final class DiscoveryFeedbackStore
{
    private PDO $pdo;

    public function __construct(?string $path = null)
    {
        $databasePath = $path ?: (getenv('DISCOVERY_FEEDBACK_SQLITE_PATH') ?: sys_get_temp_dir() . '/discovering-feedback.sqlite');
        $directory = dirname($databasePath);

        if (!is_dir($directory)) {
            @mkdir($directory, 0o777, true);
        }

        $this->pdo = new PDO('sqlite:' . $databasePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS discovery_feedback (resource TEXT NOT NULL, hit_id TEXT NOT NULL, title TEXT NOT NULL DEFAULT "", reference TEXT NOT NULL DEFAULT "", click_count INTEGER NOT NULL DEFAULT 0, last_clicked_at TEXT DEFAULT NULL, PRIMARY KEY(resource, hit_id))');
    }

    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        $statement = $this->pdo->prepare('INSERT INTO discovery_feedback (resource, hit_id, title, reference, click_count, last_clicked_at) VALUES (:resource, :hit_id, :title, :reference, 1, :last_clicked_at) ON CONFLICT(resource, hit_id) DO UPDATE SET title = excluded.title, reference = excluded.reference, click_count = discovery_feedback.click_count + 1, last_clicked_at = excluded.last_clicked_at');
        $statement->execute([
            'resource' => $resource,
            'hit_id' => $hitId,
            'title' => $title,
            'reference' => $reference,
            'last_clicked_at' => gmdate(DATE_ATOM),
        ]);

        return $this->getClickCount($resource, $hitId);
    }

    public function getClickCount(string $resource, string $hitId): int
    {
        $statement = $this->pdo->prepare('SELECT click_count FROM discovery_feedback WHERE resource = :resource AND hit_id = :hit_id');
        $statement->execute(['resource' => $resource, 'hit_id' => $hitId]);
        $result = $statement->fetchColumn();

        return is_numeric($result) ? (int) $result : 0;
    }
}

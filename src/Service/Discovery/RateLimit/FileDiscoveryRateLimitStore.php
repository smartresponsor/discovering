<?php

declare(strict_types=1);

namespace App\Service\Discovery\RateLimit;

/**
 * Lightweight fixed-window file-backed store.
 *
 * State shape:
 * {
 *   "scope|actor": {"count": 1, "resetAt": 1712160000}
 * }
 */
final class FileDiscoveryRateLimitStore
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * @return array{count:int, resetAt:int}
     */
    public function increment(string $scope, string $actorKey, int $windowSeconds): array
    {
        $this->ensureParentDirectory();

        $handle = fopen($this->path, 'c+');
        if ($handle === false) {
            throw new \RuntimeException(sprintf('Unable to open rate limit store at "%s".', $this->path));
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException(sprintf('Unable to lock rate limit store at "%s".', $this->path));
            }

            rewind($handle);
            $contents = stream_get_contents($handle);
            $state = $this->decodeState(is_string($contents) ? $contents : '');
            $now = time();

            foreach ($state as $bucket => $bucketState) {
                $resetAt = (int) ($bucketState['resetAt'] ?? 0);
                if ($resetAt <= $now) {
                    unset($state[$bucket]);
                }
            }

            $bucket = $scope . '|' . $actorKey;
            $resetAt = $now + max(1, $windowSeconds);
            $count = 0;

            if (isset($state[$bucket])) {
                $existingResetAt = (int) ($state[$bucket]['resetAt'] ?? 0);
                if ($existingResetAt > $now) {
                    $resetAt = $existingResetAt;
                    $count = (int) ($state[$bucket]['count'] ?? 0);
                }
            }

            ++$count;
            $state[$bucket] = [
                'count' => $count,
                'resetAt' => $resetAt,
            ];

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            fflush($handle);
            flock($handle, LOCK_UN);

            return [
                'count' => $count,
                'resetAt' => $resetAt,
            ];
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return array<string, array{count:int, resetAt:int}>
     */
    private function decodeState(string $contents): array
    {
        if (trim($contents) === '') {
            return [];
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $state = [];
        foreach ($decoded as $bucket => $bucketState) {
            if (!is_string($bucket) || !is_array($bucketState)) {
                continue;
            }

            $state[$bucket] = [
                'count' => (int) ($bucketState['count'] ?? 0),
                'resetAt' => (int) ($bucketState['resetAt'] ?? 0),
            ];
        }

        return $state;
    }

    private function ensureParentDirectory(): void
    {
        $directory = dirname($this->path);
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0o777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Unable to create rate limit directory "%s".', $directory));
        }
    }
}

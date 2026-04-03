<?php

declare(strict_types=1);

namespace App\Tests\Support;

use PHPUnit\Framework\TestCase;

abstract class DiscoveryTempFilesystemTestCase extends TestCase
{
    private ?string $discoveryTempRoot = null;

    protected function createTempDirectory(string $prefix): string
    {
        $path = $this->tempRoot() . '/' . $this->normalizePrefix($prefix) . bin2hex(random_bytes(6));
        if (!is_dir($path)) {
            mkdir($path, 0o777, true);
        }

        return $path;
    }

    protected function createTempFilePath(string $prefix, string $suffix = ''): string
    {
        return $this->tempRoot() . '/' . $this->normalizePrefix($prefix) . bin2hex(random_bytes(6)) . $suffix;
    }

    protected function createTempProjectDirectory(string $prefix): string
    {
        return $this->createTempDirectory($prefix);
    }

    protected function ensureDirectory(string $path): string
    {
        if (!is_dir($path)) {
            mkdir($path, 0o777, true);
        }

        return $path;
    }

    /**
     * @param array<mixed> $payload
     */
    protected function writeJsonFile(string $path, array $payload): string
    {
        $this->ensureDirectory(dirname($path));
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    /**
     * @param array<mixed> $records
     */
    protected function writeDiscoveryRegistryFile(string $projectDir, string $directoryName, string $fileName, array $records): string
    {
        return $this->writeJsonFile($projectDir . '/resources/discovery/' . trim($directoryName, '/') . '/' . ltrim($fileName, '/'), $records);
    }

    /**
     * @param array<mixed> $records
     */
    protected function writeLegacyDiscoveryRegistryFile(string $projectDir, string $fileName, array $records): string
    {
        return $this->writeJsonFile($projectDir . '/resources/discovery/' . ltrim($fileName, '/'), $records);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_string($this->discoveryTempRoot) && is_dir($this->discoveryTempRoot)) {
            $this->removeDirectoryRecursively($this->discoveryTempRoot);
        }

        $this->discoveryTempRoot = null;
    }

    private function tempRoot(): string
    {
        if (is_string($this->discoveryTempRoot)) {
            return $this->discoveryTempRoot;
        }

        $classSlug = str_replace('\\', '-', static::class);
        $this->discoveryTempRoot = sys_get_temp_dir() . '/discovering-tests/' . $classSlug . '-' . bin2hex(random_bytes(6));
        if (!is_dir($this->discoveryTempRoot)) {
            mkdir($this->discoveryTempRoot, 0o777, true);
        }

        return $this->discoveryTempRoot;
    }

    private function normalizePrefix(string $prefix): string
    {
        return trim($prefix, '/');
    }

    private function removeDirectoryRecursively(string $directory): void
    {
        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectoryRecursively($path);
                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}

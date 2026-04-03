<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;

final class FileLibsourceOperatorEventLogStore implements LibsourceOperatorEventLogStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly LibsourceOperatorEventJsonSerializer $serializer,
    ) {
    }

    public function append(LibsourceOperatorEvent $event): void
    {
        $events = $this->all();
        $events[] = $event;
        $this->persist($events);
    }

    public function all(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $contents = file_get_contents($this->path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        return $this->serializer->decodeMany($contents);
    }

    public function clear(): void
    {
        $this->persist([]);
    }

    /**
     * @param list<LibsourceOperatorEvent> $events
     */
    private function persist(array $events): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->path, $this->serializer->encodeMany($events), LOCK_EX);
    }
}

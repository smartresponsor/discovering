<?php
declare(strict_types=1);

namespace App\SearchInterface\Adapter;
interface SearchAdapterInterface {
    /** @param array<string,mixed> $doc */ public function upsert(string $index, string $id, array $doc): void;
    public function remove(string $index, string $id): void;
    /** @return array<int,array<string,mixed>> */
    public function search(string $index, string $query, int $limit=20, int $offset=0): array;
    public function createIndex(string $index): void;
    public function aliasSwap(string $from, string $to): void;
}

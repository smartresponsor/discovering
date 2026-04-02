<?php
declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryMode
{
    public const RELEVANCE = 'relevance';
    public const GOVERNANCE = 'governance';
    public const OPERATIONS = 'operations';
    public const EXPLORATION = 'exploration';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::RELEVANCE,
            self::GOVERNANCE,
            self::OPERATIONS,
            self::EXPLORATION,
        ];
    }

    public static function normalize(string $mode): string
    {
        return in_array($mode, self::values(), true) ? $mode : self::RELEVANCE;
    }
}

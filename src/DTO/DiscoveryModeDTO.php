<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery mode contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryModeDTO
{
    public const RELEVANCE = 'relevance';
    public const GOVERNANCE = 'governance';
    public const OPERATIONS = 'operations';
    public const EXPLORATION = 'exploration';

    /**
     * Returns the supported canonical discovery mode values for validation and normalization.
     *
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

    /**
     * Normalizes a requested discovery mode to a supported canonical value.
     */
    public static function normalize(string $mode): string
    {
        return in_array($mode, self::values(), true) ? $mode : self::RELEVANCE;
    }
}

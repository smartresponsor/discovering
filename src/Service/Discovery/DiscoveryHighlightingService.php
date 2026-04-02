<?php
declare(strict_types=1);

namespace App\Service\Discovery;

final class DiscoveryHighlightingService
{
    /**
     * @param list<string> $tokens
     */
    public function highlight(string $value, array $tokens): string
    {
        if ($value === '' || $tokens === []) {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $patterns = array_values(array_unique(array_filter(array_map(
            static fn (string $token): string => preg_quote($token, '/'),
            array_filter($tokens, static fn (string $token): bool => $token !== ''),
        ))));

        if ($patterns === []) {
            return $escapedValue;
        }

        return (string) preg_replace_callback(
            '/(' . implode('|', $patterns) . ')/i',
            static fn (array $matches): string => '<mark>' . $matches[0] . '</mark>',
            $escapedValue,
        );
    }
}

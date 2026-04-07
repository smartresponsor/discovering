<?php
declare(strict_types=1);

namespace App\Service\Discovery;


/**
 * Provides the discovery highlighting capability within the discovery component.
 */
final class DiscoveryHighlightingService
{
    /**
     * @param list<string> $tokens
     */
    public function highlight(string $value, array $tokens): string
    {
        $escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $patterns = $this->buildPatterns($tokens);

        if ($escapedValue === '' || $patterns === []) {
            return $escapedValue;
        }

        return (string) preg_replace_callback(
            '/(' . implode('|', $patterns) . ')/i',
            static fn (array $matches): string => '<mark>' . $matches[0] . '</mark>',
            $escapedValue,
        );
    }

    /**
     * @param list<string> $tokens
     */
    public function buildSnippet(string $content, array $tokens, int $radius = 80): string
    {
        $normalized = trim($content);
        if ($normalized === '') {
            return '';
        }

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $position = mb_stripos($normalized, $token);
            if ($position === false) {
                continue;
            }

            $start = max(0, $position - $radius);
            $length = min(mb_strlen($normalized) - $start, ($radius * 2) + mb_strlen($token));
            $snippet = mb_substr($normalized, $start, $length);

            if ($start > 0) {
                $snippet = '…' . ltrim($snippet);
            }

            if (($start + $length) < mb_strlen($normalized)) {
                $snippet = rtrim($snippet) . '…';
            }

            return $snippet;
        }

        $fallback = mb_substr($normalized, 0, min(180, mb_strlen($normalized)));

        return mb_strlen($normalized) > mb_strlen($fallback) ? rtrim($fallback) . '…' : $fallback;
    }

    /**
     * @param list<string> $tokens
     * @return list<string>
     */
    private function buildPatterns(array $tokens): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (string $token): string => preg_quote($token, '/'),
            array_filter($tokens, static fn (string $token): bool => $token !== ''),
        ))));
    }
}

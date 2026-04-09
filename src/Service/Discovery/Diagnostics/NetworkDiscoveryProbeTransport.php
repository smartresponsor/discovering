<?php

declare(strict_types=1);

namespace App\Service\Discovery\Diagnostics;

use PDO;
use Throwable;


/**
 * Provides the network discovery probe transport capability within the discovery component.
 */
final class NetworkDiscoveryProbeTransport implements DiscoveryProbeTransportInterface
{
    public function __construct(
        private readonly int $httpTimeoutSeconds = 2,
    ) {
    }

    /**
     * Performs the probe http operation for this discovery service.
     */
    public function probeHttp(string $baseUrl, ?string $apiKey = null): array
    {
        $base = rtrim(trim($baseUrl), '/');
        if ($base === '') {
            return [
                'reachable' => false,
                'details' => ['HTTP probe requires a non-empty base URL.'],
            ];
        }

        $details = [];

        foreach (['/health', '/version'] as $path) {
            $result = $this->request($base . $path, $apiKey);
            $details[] = sprintf('%s => HTTP %d', $path, $result['statusCode']);

            if ($result['statusCode'] >= 200 && $result['statusCode'] < 300) {
                return [
                    'reachable' => true,
                    'details' => $details,
                ];
            }
        }

        $details[] = 'No successful HTTP probe response was received from the configured service endpoints.';

        return [
            'reachable' => false,
            'details' => $details,
        ];
    }

    /**
     * Performs the probe pdo operation for this discovery service.
     */
    public function probePdo(string $dsn, ?string $user = null, ?string $password = null): array
    {
        if (trim($dsn) === '') {
            return [
                'reachable' => false,
                'details' => ['PDO probe requires a non-empty DSN.'],
            ];
        }

        try {
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2,
            ]);
            $statement = $pdo->query('SELECT 1');
            $value = $statement === false ? null : $statement->fetchColumn();

            return [
                'reachable' => true,
                'details' => [sprintf('PDO probe returned %s.', $value === false || $value === null ? 'no scalar value' : (string) $value)],
            ];
        } catch (Throwable $throwable) {
            return [
                'reachable' => false,
                'details' => [sprintf('%s: %s', $throwable::class, $throwable->getMessage())],
            ];
        }
    }

    /**
     * @return array{statusCode: int}
     */
    private function request(string $url, ?string $apiKey): array
    {
        $headers = [
            'Accept: application/json',
        ];

        if ($apiKey !== null && trim($apiKey) !== '') {
            $headers[] = 'Authorization: Bearer ' . trim($apiKey);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => $this->httpTimeoutSeconds,
            ],
        ]);

        @file_get_contents($url, false, $context);
        $responseHeaders = [];
        if (isset($http_response_header) && is_array($http_response_header)) {
            $responseHeaders = $http_response_header;
        }

        $statusLine = $responseHeaders !== [] ? (string) $responseHeaders[0] : '';
        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1) {
            return ['statusCode' => (int) $matches[1]];
        }

        return ['statusCode' => 0];
    }
}

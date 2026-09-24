<?php

declare(strict_types=1);

namespace App\Discovering\Service\Diagnostics;

use App\Discovering\ServiceInterface\Diagnostics\DiscoveryProbeTransportInterface; /**
 * Provides the network discovery probe transport capability within the discovery component.
 */
final class DiscoveryNetworkProbeTransport implements DiscoveryProbeTransportInterface
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
        if ('' === $base) {
            return [
                'reachable' => false,
                'details' => ['HTTP probe requires a non-empty base URL.'],
            ];
        }

        $details = [];

        foreach (['/health', '/version'] as $path) {
            $result = $this->request($base.$path, $apiKey);
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
     * @return array{statusCode: int}
     */
    private function request(string $url, ?string $apiKey): array
    {
        $headers = [
            'Accept: application/json',
        ];

        if (null !== $apiKey && '' !== trim($apiKey)) {
            $headers[] = 'Authorization: Bearer '.trim($apiKey);
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
        global $http_response_header;
        $responseHeaders = is_array($http_response_header) ? $http_response_header : [];

        $statusLine = [] !== $responseHeaders ? (string) $responseHeaders[0] : '';
        if (1 === preg_match('/\s(\d{3})\s/', $statusLine, $matches)) {
            return ['statusCode' => (int) $matches[1]];
        }

        return ['statusCode' => 0];
    }
}

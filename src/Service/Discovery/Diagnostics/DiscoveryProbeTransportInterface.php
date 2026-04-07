<?php

declare(strict_types=1);

namespace App\Service\Discovery\Diagnostics;


/**
 * Defines the contract for the discovery probe transport capability within the discovery component.
 */
interface DiscoveryProbeTransportInterface
{
    /**
     * @return array{reachable: bool, details: list<string>}
     */
    public function probeHttp(string $baseUrl, ?string $apiKey = null): array;

    /**
     * @return array{reachable: bool, details: list<string>}
     */
    public function probePdo(string $dsn, ?string $user = null, ?string $password = null): array;
}

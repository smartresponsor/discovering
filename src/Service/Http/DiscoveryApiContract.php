<?php

declare(strict_types=1);

namespace App\Discovering\Service\Http;

/**
 * Provides the discovery api contract capability within the discovery component.
 */
final class DiscoveryApiContract
{
    public const string API_VERSION = 'v1';
    public const string ENVELOPE_SCHEMA_FAMILY = 'discovery-api-envelope';
    public const string ENVELOPE_SCHEMA_VERSION = '1.0.0';
    public const string API_VERSION_HEADER = 'X-Discovery-Api-Version';
    public const string SCHEMA_FAMILY_HEADER = 'X-Discovery-Schema-Family';
    public const string SCHEMA_VERSION_HEADER = 'X-Discovery-Schema-Version';

    private function __construct()
    {
    }
}

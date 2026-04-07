<?php

declare(strict_types=1);

namespace App\Tests\Contract\Discovery;

use App\Service\Discovery\Http\DiscoveryApiContract;
use PHPUnit\Framework\Assert;

trait DiscoveryApiContractAssertions
{
    /**
     * @param array<string, mixed> $payload
     */
    private static function assertDiscoveryEnvelopeContract(
        array $payload,
        ?string $expectedSchemaFamily = null,
        string|int|null $expectedSchemaVersion = null,
    ): void {
        Assert::assertArrayHasKey('ok', $payload);
        Assert::assertArrayHasKey('apiVersion', $payload);
        Assert::assertArrayHasKey('requestId', $payload);
        Assert::assertArrayHasKey('meta', $payload);
        Assert::assertIsArray($payload['meta']);
        Assert::assertSame(DiscoveryApiContract::API_VERSION, $payload['apiVersion']);

        $schemaFamily = $expectedSchemaFamily ?? DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY;
        $schemaVersion = $expectedSchemaVersion
            ?? ($expectedSchemaFamily === null ? DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION : 1);

        Assert::assertSame($schemaFamily, $payload['meta']['schemaFamily'] ?? null);
        Assert::assertSame($schemaVersion, $payload['meta']['schemaVersion'] ?? null);
        Assert::assertArrayHasKey('canonicalPath', $payload['meta']);
        Assert::assertArrayHasKey('deprecatedAlias', $payload['meta']);

        if (($payload['ok'] ?? null) === true) {
            Assert::assertArrayHasKey('data', $payload);
        } else {
            Assert::assertArrayHasKey('error', $payload);
            Assert::assertIsArray($payload['error']);
            Assert::assertArrayHasKey('code', $payload['error']);
            Assert::assertArrayHasKey('message', $payload['error']);
            Assert::assertArrayHasKey('details', $payload['error']);
        }
    }
}

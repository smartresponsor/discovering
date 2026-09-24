<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoverySourceRecordDTO;
use App\Discovering\Factory\Document\DiscoveryDocumentFactory;
use App\Discovering\Provider\Document\DiscoveryDocumentProvider;
use App\Discovering\ServiceInterface\Source\DiscoverySourceProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery document provider test case for the Discovering component.
 */
final class DiscoveryDocumentProviderTest extends TestCase
{
    public function testItAggregatesAndSortsDocumentsFromAllSourceProviders(): void
    {
        $provider = new DiscoveryDocumentProvider(
            sourceProviders: [
                new class implements DiscoverySourceProviderInterface {
                    public function getSourceName(): string
                    {
                        return 'briefing-file-source-provider';
                    }

                    public function getResourceType(): string
                    {
                        return 'briefing';
                    }

                    public function provide(): array
                    {
                        return [
                            new DiscoverySourceRecordDTO('briefing', 'briefing-zeta', 'Zeta', 'Zeta body'),
                            new DiscoverySourceRecordDTO('briefing', 'briefing-alpha', 'Alpha', 'Alpha body'),
                        ];
                    }
                },
                new class implements DiscoverySourceProviderInterface {
                    public function getSourceName(): string
                    {
                        return 'playbook-file-source-provider';
                    }

                    public function getResourceType(): string
                    {
                        return 'playbook';
                    }

                    public function provide(): array
                    {
                        return [
                            new DiscoverySourceRecordDTO('playbook', 'playbook-beta', 'Beta', 'Beta body'),
                        ];
                    }
                },
            ],
            documentFactory: new DiscoveryDocumentFactory(),
        );

        $documents = $provider->provide();

        self::assertCount(3, $documents);
        self::assertSame(['briefing-alpha', 'briefing-zeta', 'playbook-beta'], array_map(
            static fn ($document): string => $document->id,
            $documents,
        ));
    }
}

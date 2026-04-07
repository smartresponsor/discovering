<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Document\DiscoveryDocumentFactory;
use App\Service\Discovery\Document\DiscoveryDocumentProvider;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;
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
                            new DiscoverySourceRecord('briefing', 'briefing-zeta', 'Zeta', 'Zeta body'),
                            new DiscoverySourceRecord('briefing', 'briefing-alpha', 'Alpha', 'Alpha body'),
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
                            new DiscoverySourceRecord('playbook', 'playbook-beta', 'Beta', 'Beta body'),
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

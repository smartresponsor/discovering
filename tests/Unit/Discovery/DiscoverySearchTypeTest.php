<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryModeDTO;
use App\Discovering\DTO\DiscoveryQueryDTO;
use App\Discovering\Form\DiscoverySearchType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

/**
 * Exercises the discovery search form boundary without mutating immutable query DTOs.
 */
final class DiscoverySearchTypeTest extends TestCase
{
    public function testSearchFormDoesNotMutateReadonlyDiscoveryQuery(): void
    {
        $query = new DiscoveryQueryDTO(
            query: 'governance',
            resource: 'project',
            limit: 10,
            offset: 5,
            filters: ['status' => 'active'],
            resourceWeights: ['document' => 1.25],
            mode: DiscoveryModeDTO::GOVERNANCE,
        );

        $form = Forms::createFormFactoryBuilder()
            ->addType(new DiscoverySearchType())
            ->getFormFactory()
            ->create(DiscoverySearchType::class, $query);

        $form->submit([
            'query' => 'changed',
            'mode' => DiscoveryModeDTO::OPERATIONS,
            'resource' => 'document',
            'status' => 'draft',
            'limit' => '25',
            'offset' => '0',
            'document_weight' => '2.0',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame($query, $form->getData());
        self::assertSame('governance', $query->query);
        self::assertSame('project', $query->resource);
        self::assertSame(10, $query->limit);
        self::assertSame(5, $query->offset);
        self::assertSame('active', $query->filters['status']);
        self::assertSame(1.25, $query->resourceWeights['document']);
        self::assertSame(DiscoveryModeDTO::GOVERNANCE, $query->mode);
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Operations;

use App\Discovering\Dto\Discovery\DiscoveryOperationEvent;
use App\Discovering\Entity\Discovery\DiscoveryOperationEventEntity;
use App\Discovering\ServiceInterface\Discovery\Operations\DiscoveryOperationEventLogStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Stores Discovering operation events through Doctrine for durable operational observability.
 */
final class DoctrineDiscoveryOperationEventLogStore implements DiscoveryOperationEventLogStoreInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function append(DiscoveryOperationEvent $event): void
    {
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($event): void {
            $this->ensureSchema();

            $entityManager->persist(new DiscoveryOperationEventEntity(
                eventId: $this->eventId($event),
                requestId: $event->requestId,
                channel: $event->channel,
                operation: $event->operation,
                status: $event->status,
                occurredAt: $event->occurredAt,
                contextJson: json_encode($event->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ));
        });
    }

    public function all(): array
    {
        $this->ensureSchema();

        /** @var list<DiscoveryOperationEventEntity> $entities */
        $entities = $this->entityManager->getRepository(DiscoveryOperationEventEntity::class)->findBy([], [
            'occurredAt' => 'ASC',
            'eventId' => 'ASC',
        ]);

        return array_map(fn (DiscoveryOperationEventEntity $entity): DiscoveryOperationEvent => $this->hydrate($entity), $entities);
    }

    public function latest(int $limit = 25): array
    {
        if ($limit <= 0) {
            return [];
        }

        $this->ensureSchema();

        /** @var list<DiscoveryOperationEventEntity> $entities */
        $entities = $this->entityManager->getRepository(DiscoveryOperationEventEntity::class)->findBy([], [
            'occurredAt' => 'DESC',
            'eventId' => 'DESC',
        ], $limit);

        return array_map(fn (DiscoveryOperationEventEntity $entity): DiscoveryOperationEvent => $this->hydrate($entity), $entities);
    }

    public function clear(): void
    {
        $this->ensureSchema();
        $this->entityManager->createQuery(sprintf('DELETE FROM %s e', DiscoveryOperationEventEntity::class))->execute();
    }

    private function ensureSchema(): void
    {
        if ($this->schemaReady) {
            return;
        }

        $tool = new SchemaTool($this->entityManager);
        $tool->updateSchema([
            $this->entityManager->getClassMetadata(DiscoveryOperationEventEntity::class),
        ]);
        $this->schemaReady = true;
    }

    private function hydrate(DiscoveryOperationEventEntity $entity): DiscoveryOperationEvent
    {
        $context = json_decode($entity->getContextJson(), true);

        return new DiscoveryOperationEvent(
            requestId: $entity->getRequestId(),
            channel: $entity->getChannel(),
            operation: $entity->getOperation(),
            status: $entity->getStatus(),
            occurredAt: $entity->getOccurredAt(),
            context: is_array($context) ? $context : [],
        );
    }

    private function eventId(DiscoveryOperationEvent $event): string
    {
        return substr(sha1($event->requestId.'|'.$event->operation.'|'.$event->occurredAt.'|'.json_encode($event->context)), 0, 40);
    }
}

<?php

declare(strict_types=1);

namespace App\Discovering\Repository\Libsource;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\Entity\DiscoveryLibsourceOperatorEventEntity;
use App\Discovering\ServiceInterface\Libsource\Log\DiscoveryLibsourceOperatorEventLogStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Stores libsource operator events through Doctrine for durable management event-log history.
 */
final class DiscoveryDoctrineLibsourceOperatorEventLogStore implements DiscoveryLibsourceOperatorEventLogStoreInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function append(DiscoveryLibsourceOperatorEventDTO $event): void
    {
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($event): void {
            $this->ensureSchema();

            $entityManager->persist(new DiscoveryLibsourceOperatorEventEntity(
                eventId: $this->eventId($event),
                eventName: $event->eventName,
                level: $event->level,
                summary: $event->summary,
                contextJson: json_encode($event->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ));
        });
    }

    public function all(): array
    {
        $this->ensureSchema();

        /** @var list<DiscoveryLibsourceOperatorEventEntity> $entities */
        $entities = $this->entityManager->getRepository(DiscoveryLibsourceOperatorEventEntity::class)->findBy([], [
            'eventId' => 'ASC',
        ]);

        return array_map(fn (DiscoveryLibsourceOperatorEventEntity $entity): DiscoveryLibsourceOperatorEventDTO => $this->hydrate($entity), $entities);
    }

    public function clear(): void
    {
        $this->ensureSchema();
        $this->entityManager->createQuery(sprintf('DELETE FROM %s e', DiscoveryLibsourceOperatorEventEntity::class))->execute();
    }

    private function ensureSchema(): void
    {
        if ($this->schemaReady) {
            return;
        }

        $tool = new SchemaTool($this->entityManager);
        $tool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        $this->schemaReady = true;
    }

    private function hydrate(DiscoveryLibsourceOperatorEventEntity $entity): DiscoveryLibsourceOperatorEventDTO
    {
        $context = json_decode($entity->getContextJson(), true);

        return new DiscoveryLibsourceOperatorEventDTO(
            eventName: $entity->getEventName(),
            level: $entity->getLevel(),
            summary: $entity->getSummary(),
            context: is_array($context) ? $context : [],
        );
    }

    private function eventId(DiscoveryLibsourceOperatorEventDTO $event): string
    {
        return substr(sha1($event->eventName.'|'.$event->level.'|'.$event->summary.'|'.json_encode($event->context)), 0, 40);
    }
}

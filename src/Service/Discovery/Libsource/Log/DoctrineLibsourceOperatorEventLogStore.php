<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Libsource\Log;

use App\Discovering\Dto\Discovery\LibsourceOperatorEvent;
use App\Discovering\Entity\Discovery\LibsourceOperatorEventEntity;
use App\Discovering\ServiceInterface\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Stores libsource operator events through Doctrine for durable management event-log history.
 */
final class DoctrineLibsourceOperatorEventLogStore implements LibsourceOperatorEventLogStoreInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function append(LibsourceOperatorEvent $event): void
    {
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($event): void {
            $this->ensureSchema();

            $entityManager->persist(new LibsourceOperatorEventEntity(
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

        /** @var list<LibsourceOperatorEventEntity> $entities */
        $entities = $this->entityManager->getRepository(LibsourceOperatorEventEntity::class)->findBy([], [
            'eventId' => 'ASC',
        ]);

        return array_map(fn (LibsourceOperatorEventEntity $entity): LibsourceOperatorEvent => $this->hydrate($entity), $entities);
    }

    public function clear(): void
    {
        $this->ensureSchema();
        $this->entityManager->createQuery(sprintf('DELETE FROM %s e', LibsourceOperatorEventEntity::class))->execute();
    }

    private function ensureSchema(): void
    {
        if ($this->schemaReady) {
            return;
        }

        $tool = new SchemaTool($this->entityManager);
        $tool->updateSchema([
            $this->entityManager->getClassMetadata(LibsourceOperatorEventEntity::class),
        ]);
        $this->schemaReady = true;
    }

    private function hydrate(LibsourceOperatorEventEntity $entity): LibsourceOperatorEvent
    {
        $context = json_decode($entity->getContextJson(), true);

        return new LibsourceOperatorEvent(
            eventName: $entity->getEventName(),
            level: $entity->getLevel(),
            summary: $entity->getSummary(),
            context: is_array($context) ? $context : [],
        );
    }

    private function eventId(LibsourceOperatorEvent $event): string
    {
        return substr(sha1($event->eventName.'|'.$event->level.'|'.$event->summary.'|'.json_encode($event->context)), 0, 40);
    }
}

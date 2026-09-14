<?php

declare(strict_types=1);

namespace App\Discovering\Entity\Discovery;

use App\Discovering\Repository\Discovery\LibsourceOperatorEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LibsourceOperatorEventRepository::class)]
#[ORM\Table(name: 'discovery_libsource_operator_event_log')]
/**
 * Persists operator-facing libsource event records for management diagnostics and traceability.
 */
final class LibsourceOperatorEventEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'event_id', type: 'string', length: 40)]
    private string $eventId;

    #[ORM\Column(name: 'event_name', type: 'string', length: 128)]
    private string $eventName;

    #[ORM\Column(type: 'string', length: 32)]
    private string $level;

    #[ORM\Column(type: 'text')]
    private string $summary;

    #[ORM\Column(name: 'context_json', type: 'text')]
    private string $contextJson;

    public function __construct(
        string $eventId,
        string $eventName,
        string $level,
        string $summary,
        string $contextJson,
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->level = $level;
        $this->summary = $summary;
        $this->contextJson = $contextJson;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getContextJson(): string
    {
        return $this->contextJson;
    }
}

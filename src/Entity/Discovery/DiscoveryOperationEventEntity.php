<?php

declare(strict_types=1);

namespace App\Discovering\Entity\Discovery;

use App\Discovering\Repository\Discovery\DiscoveryOperationEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoveryOperationEventRepository::class)]
#[ORM\Table(name: 'discovery_operation_event_log')]
/**
 * Persists structured Discovering operation events for auditability, diagnostics and request correlation.
 */
final class DiscoveryOperationEventEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'event_id', type: 'string', length: 40)]
    private string $eventId;

    #[ORM\Column(name: 'request_id', type: 'string', length: 128)]
    private string $requestId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $channel;

    #[ORM\Column(type: 'string', length: 191)]
    private string $operation;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(name: 'occurred_at', type: 'string', length: 64)]
    private string $occurredAt;

    #[ORM\Column(name: 'context_json', type: 'text')]
    private string $contextJson;

    public function __construct(
        string $eventId,
        string $requestId,
        string $channel,
        string $operation,
        string $status,
        string $occurredAt,
        string $contextJson,
    ) {
        $this->eventId = $eventId;
        $this->requestId = $requestId;
        $this->channel = $channel;
        $this->operation = $operation;
        $this->status = $status;
        $this->occurredAt = $occurredAt;
        $this->contextJson = $contextJson;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function getContextJson(): string
    {
        return $this->contextJson;
    }
}

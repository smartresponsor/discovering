<?php

declare(strict_types=1);

namespace App\Service\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class DiscoveryOperationLogger
{
    public const string REQUEST_ID_ATTRIBUTE = '_discovery_request_id';
    public const string REQUEST_ID_HEADER = 'X-Request-Id';

    public function __construct(
        private readonly DiscoveryOperationEventLogStoreInterface $logStore,
        private readonly RequestStack $requestStack,
        private readonly DiscoveryAdapterInterface $adapter,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function recordHttp(string $operation, string $status = 'ok', array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $requestId = $request instanceof Request ? $this->resolveRequestId($request) : $this->generateConsoleRequestId();

        $baseContext = [
            'backendName' => $this->adapter->getBackendName(),
        ];

        if ($request instanceof Request) {
            $baseContext['method'] = $request->getMethod();
            $baseContext['path'] = $request->getPathInfo();
            $route = $request->attributes->get('_route');
            if (is_string($route) && $route !== '') {
                $baseContext['route'] = $route;
            }
        }

        $this->logStore->append(new DiscoveryOperationEvent(
            requestId: $requestId,
            channel: 'http',
            operation: $operation,
            status: $status,
            occurredAt: gmdate(DATE_ATOM),
            context: $baseContext + $context,
        ));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function recordConsole(string $operation, string $status = 'ok', array $context = []): void
    {
        $this->logStore->append(new DiscoveryOperationEvent(
            requestId: $this->generateConsoleRequestId(),
            channel: 'console',
            operation: $operation,
            status: $status,
            occurredAt: gmdate(DATE_ATOM),
            context: ['backendName' => $this->adapter->getBackendName()] + $context,
        ));
    }

    private function resolveRequestId(Request $request): string
    {
        $requestId = $request->attributes->get(self::REQUEST_ID_ATTRIBUTE);
        if (is_string($requestId) && $requestId !== '') {
            return $requestId;
        }

        $headerRequestId = trim((string) $request->headers->get(self::REQUEST_ID_HEADER, ''));
        if ($headerRequestId !== '') {
            $request->attributes->set(self::REQUEST_ID_ATTRIBUTE, $headerRequestId);

            return $headerRequestId;
        }

        $requestId = 'req-' . bin2hex(random_bytes(8));
        $request->attributes->set(self::REQUEST_ID_ATTRIBUTE, $requestId);

        return $requestId;
    }

    private function generateConsoleRequestId(): string
    {
        return 'console-' . bin2hex(random_bytes(8));
    }
}

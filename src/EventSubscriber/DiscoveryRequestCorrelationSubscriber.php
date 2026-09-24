<?php

declare(strict_types=1);

namespace App\Discovering\EventSubscriber;

use App\Discovering\Factory\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Service\Operations\DiscoveryOperationLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applies discovery request correlation behavior to the discovery HTTP or kernel event pipeline.
 */
final class DiscoveryRequestCorrelationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 128],
            KernelEvents::RESPONSE => ['onKernelResponse', -128],
        ];
    }

    /**
     * Applies the on kernel request event handling step for this subscriber.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isDiscoveryPath($request->getPathInfo())) {
            return;
        }

        $requestId = trim((string) $request->headers->get(DiscoveryOperationLogger::REQUEST_ID_HEADER, ''));
        if ('' === $requestId) {
            $requestId = 'req-'.bin2hex(random_bytes(8));
        }

        $request->attributes->set(DiscoveryOperationLogger::REQUEST_ID_ATTRIBUTE, $requestId);
    }

    /**
     * Applies the on kernel response event handling step for this subscriber.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isDiscoveryPath($request->getPathInfo())) {
            return;
        }

        $requestId = $request->attributes->get(DiscoveryOperationLogger::REQUEST_ID_ATTRIBUTE);
        if (is_string($requestId) && '' !== $requestId) {
            $event->getResponse()->headers->set(DiscoveryOperationLogger::REQUEST_ID_HEADER, $requestId);
        }

        $event->getResponse()->headers->set(
            DiscoveryJsonResponseFactory::API_VERSION_HEADER,
            DiscoveryJsonResponseFactory::API_VERSION,
        );
    }

    private function isDiscoveryPath(string $path): bool
    {
        return str_starts_with($path, '/discovery')
            || str_starts_with($path, '/api/v1/discovery')
            || str_starts_with($path, '/api/discovery')
            || str_starts_with($path, '/management/discovery');
    }
}

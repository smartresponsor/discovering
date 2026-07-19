<?php

declare(strict_types=1);

namespace App\Discovering\Subscriber\Discovery;

use App\Discovering\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Enforces minimum content-type and payload-shape expectations for mutable discovery endpoints.
 */
final class DiscoveryMutationRequestHardeningSubscriber implements EventSubscriberInterface
{
    private const int MAX_MUTATION_BYTES = 65536;

    public function __construct(private readonly DiscoveryJsonResponseFactory $jsonResponseFactory)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 48]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isMutableDiscoveryPath($request)) {
            return;
        }

        $contentLength = (int) $request->headers->get('Content-Length', '0');
        if ($contentLength > self::MAX_MUTATION_BYTES) {
            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_payload_too_large',
                message: 'Discovery mutation payload exceeds the supported size budget.',
                status: Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                details: ['maxBytes' => self::MAX_MUTATION_BYTES],
            ));

            return;
        }

        $rawContentType = trim((string) $request->headers->get('Content-Type', ''));
        if ('' === $rawContentType && 0 === $contentLength) {
            return;
        }

        $contentType = strtolower(trim(strtok($rawContentType, ';') ?: ''));
        $allowed = [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ];

        if (!in_array($contentType, $allowed, true)) {
            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_unsupported_media_type',
                message: 'Unsupported discovery mutation content type.',
                status: Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                details: ['allowedContentTypes' => $allowed],
            ));
        }
    }

    private function isMutableDiscoveryPath(Request $request): bool
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            return false;
        }

        $path = $request->getPathInfo();

        return '/api/discovery/click' === $path
            || '/api/discovery/click' === $path
            || '/management/discovery/rebuild' === $path;
    }
}

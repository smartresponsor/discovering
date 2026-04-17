<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Http\DiscoveryRequestSurfacePolicy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Protects discovery management and mutation endpoints.
 *
 * The subscriber enforces operator and API write tokens, accepts only
 * supported mutation content types, and rejects oversized mutation payloads
 * before controller execution.
 */
final class DiscoveryEndpointSecuritySubscriber implements EventSubscriberInterface
{
    private const int MAX_MUTATION_PAYLOAD_BYTES = 65536;

    /** @var list<string> */
    private const array ACCEPTED_MUTATION_CONTENT_TYPES = [
        'application/json',
        'application/x-www-form-urlencoded',
        'multipart/form-data',
    ];

    public function __construct(
        private readonly DiscoveryRequestSurfacePolicy $requestSurfacePolicy,
        private readonly string $managementToken,
        private readonly string $apiWriteToken,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 64]];
    }

    /**
     * Applies request-time protection and mutation validation before dispatch.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if ($this->requestSurfacePolicy->isProtectedManagementPath($path) && !$this->hasExpectedToken($request, 'X-Discovery-Management-Token', $this->managementToken)) {
            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_management_forbidden',
                message: 'Forbidden discovery management request.',
                status: Response::HTTP_FORBIDDEN,
            ));

            return;
        }

        if ($this->requestSurfacePolicy->isProtectedMutationPath($request, $path)) {
            if (!$this->hasAcceptedMutationContentType($request)) {
                $event->setResponse($this->jsonResponseFactory->error(
                    code: 'discovery_mutation_unsupported_content_type',
                    message: 'Unsupported discovery mutation content type.',
                    status: Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                ));

                return;
            }

            if ($this->mutationPayloadBytes($request) > self::MAX_MUTATION_PAYLOAD_BYTES) {
                $event->setResponse($this->jsonResponseFactory->error(
                    code: 'discovery_mutation_payload_too_large',
                    message: 'Discovery mutation payload exceeds the allowed size.',
                    status: Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                    details: [
                        'maxBytes' => self::MAX_MUTATION_PAYLOAD_BYTES,
                    ],
                ));

                return;
            }
        }

        if ($this->requestSurfacePolicy->isProtectedApiWritePath($request, $path) && !$this->hasExpectedToken($request, 'X-Discovery-Api-Write-Token', $this->apiWriteToken)) {
            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_api_write_unauthorized',
                message: 'Unauthorized discovery API write request.',
                status: Response::HTTP_UNAUTHORIZED,
            ));
        }
    }

    private function hasExpectedToken(Request $request, string $headerName, string $expectedToken): bool
    {
        if ('' === $expectedToken) {
            return false;
        }

        $providedToken = trim((string) $request->headers->get($headerName, ''));

        return hash_equals($expectedToken, $providedToken);
    }

    private function hasAcceptedMutationContentType(Request $request): bool
    {
        $contentType = strtolower(trim((string) $request->headers->get('Content-Type', '')));
        if ('' === $contentType) {
            return false;
        }

        foreach (self::ACCEPTED_MUTATION_CONTENT_TYPES as $acceptedType) {
            if ($contentType === $acceptedType || str_starts_with($contentType, $acceptedType.';')) {
                return true;
            }
        }

        return false;
    }

    private function mutationPayloadBytes(Request $request): int
    {
        $contentLength = $request->headers->get('Content-Length');
        if (is_string($contentLength) && ctype_digit($contentLength)) {
            return (int) $contentLength;
        }

        return strlen((string) $request->getContent());
    }
}

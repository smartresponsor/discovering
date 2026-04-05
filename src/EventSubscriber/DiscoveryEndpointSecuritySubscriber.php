<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applies lightweight header-token protection to mutable and management discovery endpoints.
 */
final class DiscoveryEndpointSecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $managementToken,
        private readonly string $apiWriteToken,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 64]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if ($this->isProtectedManagementPath($path)) {
            if ($this->hasExpectedToken($request, 'X-Discovery-Management-Token', $this->managementToken)) {
                return;
            }

            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_management_unauthorized',
                message: 'Unauthorized discovery management request.',
                status: Response::HTTP_UNAUTHORIZED,
            ));

            return;
        }

        if ($this->isProtectedApiWritePath($request, $path)) {
            if ($this->hasExpectedToken($request, 'X-Discovery-Api-Write-Token', $this->apiWriteToken)) {
                return;
            }

            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_api_write_unauthorized',
                message: 'Unauthorized discovery API write request.',
                status: Response::HTTP_UNAUTHORIZED,
            ));
        }
    }

    private function isProtectedManagementPath(string $path): bool
    {
        return str_starts_with($path, '/management/discovery');
    }

    private function isProtectedApiWritePath(Request $request, string $path): bool
    {
        return $request->isMethod(Request::METHOD_POST)
            && ($path === '/api/discovery/click' || $path === '/api/v1/discovery/click');
    }

    private function hasExpectedToken(Request $request, string $headerName, string $expectedToken): bool
    {
        if ($expectedToken === '') {
            return false;
        }

        $providedToken = trim((string) $request->headers->get($headerName, ''));
        if ($providedToken === '') {
            return false;
        }

        return hash_equals($expectedToken, $providedToken);
    }
}

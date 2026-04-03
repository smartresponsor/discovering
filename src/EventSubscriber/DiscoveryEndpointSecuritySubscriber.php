<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DiscoveryEndpointSecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $managementToken,
        private readonly string $apiWriteToken,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
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

            $event->setResponse(new Response('Forbidden discovery management request.', Response::HTTP_FORBIDDEN));

            return;
        }

        if ($this->isProtectedApiWritePath($request, $path)) {
            if ($this->hasExpectedToken($request, 'X-Discovery-Api-Write-Token', $this->apiWriteToken)) {
                return;
            }

            $event->setResponse(new JsonResponse([
                'ok' => false,
                'error' => 'Unauthorized discovery API write request.',
            ], Response::HTTP_UNAUTHORIZED));
        }
    }

    private function isProtectedManagementPath(string $path): bool
    {
        return str_starts_with($path, '/management/discovery');
    }

    private function isProtectedApiWritePath(Request $request, string $path): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $path === '/api/discovery/click';
    }

    private function hasExpectedToken(Request $request, string $headerName, string $expectedToken): bool
    {
        if ($expectedToken === '') {
            return false;
        }

        $providedToken = trim((string) $request->headers->get($headerName, ''));

        return hash_equals($expectedToken, $providedToken);
    }
}

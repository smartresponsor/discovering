<?php

declare(strict_types=1);

namespace App\Discovering\Subscriber\Discovery;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applies discovery response security headers behavior to the discovery HTTP or kernel event pipeline.
 */
final class DiscoveryResponseSecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
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

        $response = $event->getResponse();
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
    }

    private function isDiscoveryPath(string $path): bool
    {
        return str_starts_with($path, '/discovery')
            || str_starts_with($path, '/api/discovery')
            || str_starts_with($path, '/api/discovery')
            || str_starts_with($path, '/management/discovery');
    }
}

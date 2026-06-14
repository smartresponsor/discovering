<?php

declare(strict_types=1);

namespace App\Subscriber\Discovery;

use App\Dto\Discovery\DiscoveryRateLimitDecision;
use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Service\Discovery\RateLimit\DiscoveryRateLimiter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applies discovery rate limit behavior to the discovery HTTP or kernel event pipeline.
 */
final class DiscoveryRateLimitSubscriber implements EventSubscriberInterface
{
    private const string REQUEST_ATTRIBUTE = '_discovery_rate_limit_decision';

    public function __construct(
        private readonly DiscoveryRateLimiter $rateLimiter,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 32],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
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
        $decision = $this->rateLimiter->consumeForRequest($request);
        if (!$decision instanceof DiscoveryRateLimitDecision) {
            return;
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $decision);

        if (!$decision->exceeded) {
            return;
        }

        $this->operationLogger->recordHttp('discovery.rate_limit.denied', status: 'rate_limited', context: $decision->toArray());

        if ($this->wantsJsonResponse($request->getPathInfo())) {
            $event->setResponse($this->jsonResponseFactory->error(
                code: 'discovery_rate_limited',
                message: 'Discovery request rate limited.',
                status: Response::HTTP_TOO_MANY_REQUESTS,
                details: [
                    'scope' => $decision->scope,
                    'limit' => $decision->limit,
                    'remaining' => $decision->remaining,
                    'retryAfterSeconds' => $decision->retryAfterSeconds,
                    'resetAt' => $decision->resetAt,
                ],
            ));

            return;
        }

        $event->setResponse(new Response(
            content: 'Too many discovery requests.',
            status: Response::HTTP_TOO_MANY_REQUESTS,
        ));
    }

    /**
     * Applies the on kernel response event handling step for this subscriber.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $decision = $event->getRequest()->attributes->get(self::REQUEST_ATTRIBUTE);
        if (!$decision instanceof DiscoveryRateLimitDecision) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('X-RateLimit-Limit', (string) $decision->limit);
        $response->headers->set('X-RateLimit-Remaining', (string) $decision->remaining);
        $response->headers->set('X-RateLimit-Reset', (string) $decision->resetAt);
        $response->headers->set('X-RateLimit-Scope', $decision->scope);

        if ($decision->exceeded) {
            $response->headers->set('Retry-After', (string) $decision->retryAfterSeconds);
        }
    }

    private function wantsJsonResponse(string $path): bool
    {
        return str_starts_with($path, '/api/')
            || '/management/discovery/rebuild' === $path
            || str_ends_with($path, '/export')
            || str_contains($path, '/inspect/');
    }
}

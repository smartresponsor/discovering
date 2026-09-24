<?php

declare(strict_types=1);

namespace App\Discovering\Policy\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the discovery request surface policy capability within the discovery component.
 */
final class DiscoveryRequestSurfacePolicy
{
    public function resolveScope(Request $request): ?string
    {
        $path = $request->getPathInfo();

        if ($this->isProtectedApiWritePath($request, $path)) {
            return 'write';
        }

        if ($this->isProtectedManagementMutationPath($request, $path)) {
            return 'management_mutation';
        }

        if ($this->isQueryPath($request, $path)) {
            return 'query';
        }

        return null;
    }

    public function isProtectedManagementPath(string $path): bool
    {
        return str_starts_with($path, '/management/discovery');
    }

    public function isProtectedManagementMutationPath(Request $request, string $path): bool
    {
        if (!$this->isProtectedManagementPath($path)) {
            return false;
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            return true;
        }

        $action = $request->query->get('action');

        return is_string($action) && '' !== trim($action);
    }

    public function isProtectedApiWritePath(Request $request, string $path): bool
    {
        return $request->isMethod(Request::METHOD_POST)
            && ('/api/v1/discovery/click' === $path || '/api/discovery/click' === $path);
    }

    public function isProtectedMutationPath(Request $request, string $path): bool
    {
        return $this->isProtectedApiWritePath($request, $path)
            || $this->isProtectedManagementMutationPath($request, $path);
    }

    public function isQueryPath(Request $request, string $path): bool
    {
        if ($request->isMethod(Request::METHOD_GET) && ('/api/v1/discovery' === $path || '/api/discovery' === $path)) {
            return true;
        }

        return ($request->isMethod(Request::METHOD_GET) || $request->isMethod(Request::METHOD_POST))
            && '/discovery' === $path;
    }

    public function wantsJsonResponse(string $path): bool
    {
        return str_starts_with($path, '/api/')
            || '/management/discovery/rebuild' === $path
            || str_ends_with($path, '/export')
            || str_contains($path, '/inspect/');
    }
}

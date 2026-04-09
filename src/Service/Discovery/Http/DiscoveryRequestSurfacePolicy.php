<?php

declare(strict_types=1);

namespace App\Service\Discovery\Http;

use Symfony\Component\HttpFoundation\Request;

<<<<<<< HEAD
=======

>>>>>>> 9b0ac77d540366c141d14e49a6fb3353ed869440
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

<<<<<<< HEAD
        return is_string($action) && '' !== trim($action);
=======
        return is_string($action) && trim($action) !== '';
>>>>>>> 9b0ac77d540366c141d14e49a6fb3353ed869440
    }

    public function isProtectedApiWritePath(Request $request, string $path): bool
    {
        return $request->isMethod(Request::METHOD_POST)
<<<<<<< HEAD
            && ('/api/discovery/click' === $path || '/api/v1/discovery/click' === $path);
=======
            && ($path === '/api/discovery/click' || $path === '/api/v1/discovery/click');
>>>>>>> 9b0ac77d540366c141d14e49a6fb3353ed869440
    }

    public function isProtectedMutationPath(Request $request, string $path): bool
    {
        return $this->isProtectedApiWritePath($request, $path)
            || $this->isProtectedManagementMutationPath($request, $path);
    }

    public function isQueryPath(Request $request, string $path): bool
    {
<<<<<<< HEAD
        if ($request->isMethod(Request::METHOD_GET) && ('/api/discovery' === $path || '/api/v1/discovery' === $path)) {
=======
        if ($request->isMethod(Request::METHOD_GET) && ($path === '/api/discovery' || $path === '/api/v1/discovery')) {
>>>>>>> 9b0ac77d540366c141d14e49a6fb3353ed869440
            return true;
        }

        return ($request->isMethod(Request::METHOD_GET) || $request->isMethod(Request::METHOD_POST))
<<<<<<< HEAD
            && '/discovery' === $path;
=======
            && $path === '/discovery';
>>>>>>> 9b0ac77d540366c141d14e49a6fb3353ed869440
    }

    public function wantsJsonResponse(string $path): bool
    {
        return str_starts_with($path, '/api/')
<<<<<<< HEAD
            || '/management/discovery/rebuild' === $path
=======
            || $path === '/management/discovery/rebuild'
>>>>>>> 9b0ac77d540366c141d14e49a6fb3353ed869440
            || str_ends_with($path, '/export')
            || str_contains($path, '/inspect/');
    }
}

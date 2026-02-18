<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectOldUrls
{
    /**
     * Handle incoming request and redirect old URLs (without /en or /id) to new URLs with locale prefix.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();
        
        // Check if path starts with /en/ or /id/ - if yes, it's a new-style URL, let it through
        if (preg_match('#^/(en|id)(/|$)#', $path)) {
            return $next($request);
        }

        // Skip root path - it will be handled by routes
        if ($path === '/') {
            return $next($request);
        }

        // List of public route patterns that should redirect to locale-prefixed versions
        $publicRoutes = [
            'expose', 'ngopini', 'artikel', 'fellowship', 'cbi',
            'artikel-fellowship', 'artikel-landing', 'ngopini-artikel'
        ];

        // Check if the path starts with any of these public routes
        $pathSegments = array_filter(explode('/', $path));
        $firstSegment = $pathSegments[1] ?? null;

        if ($firstSegment && in_array($firstSegment, $publicRoutes)) {
            // Redirect old URL to new URL with /en/ prefix by default
            // Or use session locale if available
            $locale = session('locale') ?? 'en';
            $newPath = '/' . $locale . $path;
            return redirect($newPath, 301); // 301 = permanent redirect for SEO
        }

        return $next($request);
    }
}

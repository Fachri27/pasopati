<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectOldPublicUrls
{
    /**
     * Redirect old URLs without /en or /id prefix to new URLs with locale prefix.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();
        
        // Skip if already new-style (has /en/ or /id/ right after domain)
        if (preg_match('#^/(en|id)(/|$)#', $path)) {
            return $next($request);
        }

        // Redirect bare root to default locale (Indonesian)
        if ($path === '/' || $path === '') {
            return redirect('/id/', 302);
        }

        // Skip these paths - they don't need locale prefix
        if (preg_match('#^/(dashboard|admin|login|logout|pages|kategori|user|laravel-filemanager|upload-editor-image|api|storage)#', $path)) {
            return $next($request);
        }

        // List of old public route patterns that need redirect
        $publicRoutes = [
            'expose', 'ngopini', 'artikel', 'fellowship', 'cbi',
            'artikel-fellowship', 'artikel-landing', 'ngopini-artikel'
        ];

        $pathSegments = array_filter(explode('/', $path));
        $firstSegment = $pathSegments[1] ?? null;

        // Only redirect if the first path segment matches a public route
        if ($firstSegment && in_array($firstSegment, $publicRoutes)) {
            // Use Indonesian as default locale for old URLs
            $newPath = '/id' . $path;
            
            // Preserve query string
            if ($request->getQueryString()) {
                $newPath .= '?' . $request->getQueryString();
            }
            
            // Return 301 permanent redirect for SEO
            return redirect($newPath, 301);
        }

        return $next($request);
    }
}

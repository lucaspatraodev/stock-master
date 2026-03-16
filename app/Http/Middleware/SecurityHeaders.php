<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $isLocal = app()->environment('local');

        $styleSrc = ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'];
        $fontSrc = ["'self'", 'data:', 'https://fonts.bunny.net'];
        $scriptSrc = ["'self'"];
        $connectSrc = ["'self'", 'ws:', 'wss:'];

        if ($isLocal) {
            $scriptSrc[] = "'unsafe-inline'";
            $scriptSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'ws://localhost:5173';
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "img-src 'self' data: blob:",
            'font-src ' . implode(' ', $fontSrc),
            'style-src ' . implode(' ', $styleSrc),
            'script-src ' . implode(' ', $scriptSrc),
            'connect-src ' . implode(' ', $connectSrc),
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}

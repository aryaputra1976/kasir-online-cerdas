<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        if (! $headers->has('Referrer-Policy')) {
            $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        $headers->set(
            'Content-Security-Policy-Report-Only',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        );

        $isHttps = $request->isSecure()
            || $request->server('HTTPS') === 'on'
            || $request->headers->get('X-Forwarded-Proto') === 'https';

        if (app()->environment('production') && $isHttps) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}

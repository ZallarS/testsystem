<?php

    namespace App\Middleware;

    use App\Core\Response;

    class SecurityHeadersMiddleware
    {
        public function handle($next)
        {
            $response = $next();

            if ($response instanceof Response) {
                // Content Security Policy
                $csp = [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline'",
                    "style-src 'self' 'unsafe-inline'",
                    "img-src 'self' data: https:",
                    "font-src 'self'",
                    "connect-src 'self'",
                    "object-src 'none'",
                    "base-uri 'self'",
                    "form-action 'self'",
                    "frame-ancestors 'none'",
                    "block-all-mixed-content"
                ];

                header("Content-Security-Policy: " . implode("; ", $csp));
                header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
                header("X-Content-Type-Options: nosniff");
                header("X-Frame-Options: DENY");
                header("X-XSS-Protection: 1; mode=block");
                header("Referrer-Policy: strict-origin-when-cross-origin");
                header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
            }

            return $response;
        }
    }
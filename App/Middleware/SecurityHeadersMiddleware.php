<?php

    namespace App\Middleware;

    use App\Core\Response;

    class SecurityHeadersMiddleware
    {
        public function handle($next)
        {
            $response = $next();

            if ($response instanceof Response) {
                // Устанавливаем security headers
                header("X-Content-Type-Options: nosniff");
                header("X-Frame-Options: DENY");
                header("X-XSS-Protection: 1; mode=block");
                header("Referrer-Policy: strict-origin-when-cross-origin");

                // Content Security Policy
                $csp = [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline'", // unsafe-inline для простоты, но лучше убрать в production
                    "style-src 'self' 'unsafe-inline'",
                    "img-src 'self' data: https:",
                    "font-src 'self'",
                    "connect-src 'self'",
                    "object-src 'none'",
                    "base-uri 'self'",
                    "form-action 'self'",
                    "frame-ancestors 'none'"
                ];

                header("Content-Security-Policy: " . implode("; ", $csp));
            }

            return $response;
        }
    }
<?php

    namespace App\Middleware;

    use App\Core\Response;

    class SecurityHeadersMiddleware
    {
        public function handle($next)
        {
            // Устанавливаем заголовки безопасности
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: DENY");
            header("X-XSS-Protection: 1; mode=block");

            // Content Security Policy
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline'", // Осторожно с unsafe-inline!
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'"
            ];

            header("Content-Security-Policy: " . implode("; ", $csp));

            return $next();
        }
    }
<?php

    namespace App\Middleware;

    use App\Core\Response;

    class SecurityHeadersMiddleware
    {
        public function handle($next)
        {
            // Устанавливаем security headers
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: DENY");
            header("X-XSS-Protection: 1; mode=block");
            header("Referrer-Policy: strict-origin-when-cross-origin");

            // Content Security Policy
            $nonce = base64_encode(random_bytes(16));

            $csp = [
                "default-src 'self'",
                "script-src 'self' 'nonce-$nonce'",
                "style-src 'self' 'unsafe-inline'", // Разрешаем inline стили для совместимости
                "img-src 'self' data: https:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'"
            ];

            header("Content-Security-Policy: " . implode("; ", $csp));

            return $next();
        }
    }
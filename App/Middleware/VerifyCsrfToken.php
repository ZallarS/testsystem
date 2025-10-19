<?php

    namespace App\Middleware;

    use App\Core\CSRF;
    use App\Core\Response;

    class VerifyCsrfToken
    {
        private $excludedRoutes = [
            '/api/webhooks',
            '/api/stripe-events'
        ];

        public function handle($next)
        {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';

            // Расширяем список безопасных методов
            if (in_array($requestMethod, ['GET', 'HEAD', 'OPTIONS']) || $this->isExcluded($requestUri)) {
                return $next();
            }

            // Усиливаем проверку Origin
            if (!$this->isValidOrigin()) {
                error_log("CSRF: Invalid origin for request to $requestUri");
                return Response::make('Invalid request origin', 403);
            }

            $token = $this->getTokenFromRequest();

            try {
                \App\Core\CSRF::validateToken($token);
            } catch (\Exception $e) {
                error_log("CSRF validation failed: " . $e->getMessage());
                return Response::make('CSRF token validation failed', 403);
            }

            return $next();
        }

        private function isExcluded($uri)
        {
            foreach ($this->excludedRoutes as $excluded) {
                if (strpos($uri, $excluded) === 0) {
                    return true;
                }
            }
            return false;
        }

        private function isValidOrigin()
        {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $referer = $_SERVER['HTTP_REFERER'] ?? '';

            $allowedDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';

            // Более строгая проверка
            if ($origin && parse_url($origin, PHP_URL_HOST) !== $allowedDomain) {
                return false;
            }

            if ($referer && parse_url($referer, PHP_URL_HOST) !== $allowedDomain) {
                return false;
            }

            return true;
        }

        private function getTokenFromRequest()
        {
            // Приоритет: заголовок > POST > GET
            return $_SERVER['HTTP_X_CSRF_TOKEN'] ??
                ($_POST['csrf_token'] ??
                    ($_GET['csrf_token'] ?? ''));
        }
    }
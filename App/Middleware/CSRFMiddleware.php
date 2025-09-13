<?php

    namespace App\Middleware;

    use App\Core\CSRF;
    use App\Core\Response;

    class CSRFMiddleware
    {
        public function handle($next)
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $token = $_POST['csrf_token'] ?? '';
                try {
                    CSRF::validateToken($token);
                } catch (\Exception $e) {
                    return Response::make('CSRF token validation failed', 403);
                }
            }
            return $next();
        }
    }
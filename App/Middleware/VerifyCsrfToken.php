<?php

    namespace App\Middleware;

    use App\Core\CSRF;
    use App\Core\Response;

    class VerifyCsrfToken
    {
        public function handle($next)
        {
            // Проверяем все модифицирующие методы, не только POST
            $modifyingMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

            if (in_array($_SERVER['REQUEST_METHOD'], $modifyingMethods)) {
                $token = $_POST['csrf_token'] ??
                    ($_SERVER['HTTP_X_CSRF_TOKEN'] ??
                        ($_GET['csrf_token'] ?? ''));

                try {
                    CSRF::validateToken($token);
                } catch (\Exception $e) {
                    return Response::make('CSRF token validation failed', 403);
                }
            }

            return $next();
        }
    }
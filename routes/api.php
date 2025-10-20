<?php

        use App\Core\Response;
        use App\Middleware\VerifyCsrfToken;
        use App\Middleware\RateLimitMiddleware;

        // Группа для API маршрутов с CSRF защитой
        $router->group('/api', function() use ($router) {
            $router->group('/v1', function() use ($router) {

                // Public endpoints
                $router->get('/status', function() {
                    return Response::json([
                        'status' => 'ok',
                        'timestamp' => time(),
                        'version' => '1.0'
                    ]);
                });

                // Protected endpoints require CSRF
                $router->get('/users', function() {
                    // Проверка аутентификации
                    if (!\App\Core\User::isLoggedIn()) {
                        return Response::json(['error' => 'Unauthorized'], 401);
                    }

                    // Проверка прав
                    if (!\App\Core\User::isAdmin()) {
                        return Response::json(['error' => 'Forbidden'], 403);
                    }

                    $userModel = new \App\Models\User();
                    $users = $userModel->all();

                    // Удаляем чувствительные данные
                    $safeUsers = array_map(function($user) {
                        unset($user['password']);
                        unset($user['remember_token']);
                        return $user;
                    }, $users);

                    return Response::json($safeUsers);
                })->middleware([new VerifyCsrfToken()]);

                $router->get('/users/{id}', function($id) {
                    // Проверка аутентификации
                    if (!\App\Core\User::isLoggedIn()) {
                        return Response::json(['error' => 'Unauthorized'], 401);
                    }

                    $userModel = new \App\Models\User();
                    $user = $userModel->find($id);

                    if (!$user) {
                        return Response::json(['error' => 'User not found'], 404);
                    }

                    // Проверка прав доступа
                    $currentUser = \App\Core\User::get();
                    if (!$currentUser['is_admin'] && $currentUser['id'] != $user['id']) {
                        return Response::json(['error' => 'Forbidden'], 403);
                    }

                    // Удаляем чувствительные данные
                    unset($user['password']);
                    unset($user['remember_token']);

                    return Response::json($user);
                })->middleware([new VerifyCsrfToken()]);

            });
        }, ['middleware' => [
            new RateLimitMiddleware(100, 3600), // 100 запросов в час
            new \App\Middleware\SecurityHeadersMiddleware()
        ]]);

    $router->get('/health', function() {
        $healthChecker = new \App\Core\HealthChecker();
        return $healthChecker->getStatus();
    });

    $router->get('/health/detailed', function() {
        $healthChecker = new \App\Core\HealthChecker();
        $results = $healthChecker->check();

        return Response::json($results, $results['status'] === 'healthy' ? 200 : 503);
    })->middleware([new \App\Middleware\AuthMiddleware()]);
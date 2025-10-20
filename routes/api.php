<?php

    use App\Core\Response;
    use App\Middleware\RateLimitMiddleware;

    // Apply rate limiting to all API routes
    $router->group('/api', function() use ($router) {
        $router->group('/v1', function() use ($router) {

            // Public endpoints with rate limiting
            $router->get('/status', function() {
                return Response::json(['status' => 'ok', 'timestamp' => time()]);
            })->middleware([new RateLimitMiddleware(60, 60)]); // 60 requests per minute

            // Protected endpoints
            $router->get('/users', function() {
                // Validate authentication
                if (!\App\Core\User::isLoggedIn()) {
                    return Response::json(['error' => 'Unauthorized'], 401);
                }

                // Check permissions
                if (!\App\Core\User::isAdmin()) {
                    return Response::json(['error' => 'Insufficient permissions'], 403);
                }

                $userModel = new \App\Models\User();
                $users = $userModel->all();

                // Sanitize output - remove sensitive data
                $safeUsers = array_map(function($user) {
                    unset($user['password']);
                    unset($user['remember_token']);
                    return $user;
                }, $users);

                return Response::json($safeUsers);
            })->middleware([new RateLimitMiddleware(30, 60)]); // 30 requests per minute

        });
    }, ['middleware' => [new \App\Middleware\SecurityHeadersMiddleware()]]);
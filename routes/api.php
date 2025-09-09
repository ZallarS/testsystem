<?php

    use App\Core\Response;

    // Группа для API маршрутов
    $router->group('/api', function() use ($router) {
        $router->group('/v1', function() use ($router) {
            // Маршруты для пользователей
            $router->get('/users', function() {
                $userModel = new \App\Models\User();
                $users = $userModel->all();
                return Response::json($users);
            });

            $router->get('/users/{id}', function($id) {
                $userModel = new \App\Models\User();
                $user = $userModel->find($id);

                if (!$user) {
                    return Response::json(['error' => 'User not found'], 404);
                }

                return Response::json($user);
            });
        });

    });
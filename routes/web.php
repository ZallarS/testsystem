<?php

    use App\Core\Response;
    use App\Controllers\AuthController;
    use App\Controllers\Admin\AdminController;
    use App\Controllers\Admin\UsersController;
    use App\Middleware\AuthMiddleware;
    use App\Middleware\RoleMiddleware;

    // Главная страница
    $router->get('/', function() {
        return Response::view('home/index', [
            'title' => \App\Core\User::isLoggedIn() ? 'Главная - MyApp' : 'MyApp - Добро пожаловать',
            'activeMenu' => 'home'
        ]);
    });

    // Группа для маршрутов аутентификации
    $router->group('', function() use ($router) {
        $router->post('/login', [AuthController::class, 'processLogin'])->middleware([new \App\Middleware\CSRFMiddleware()]);
        $router->post('/register', [AuthController::class, 'processRegister'])->middleware([new \App\Middleware\CSRFMiddleware()]);

        $router->get('/login', function() {
            $controller = new AuthController();
            return $controller->login();
        });

        $router->get('/register', function() {
            $controller = new AuthController();
            return $controller->register();
        });

        $router->get('/logout', function() {
            $controller = new AuthController();
            return $controller->logout();
        });

    });

    // Профиль пользователя (для обычных пользователей)
    $router->get('/profile', function() {
        if (!\App\Core\User::isLoggedIn()) {
            return Response::redirect('/login');
        }

        return Response::view('profile/index', [
            'title' => 'Личный кабинет',
            'activeMenu' => 'profile'
        ]);
    })->middleware([AuthMiddleware::class]);

    // Административная группа
    $router->group('/admin', function() use ($router) {
        $router->get('', [AdminController::class, 'dashboard']);
        $router->get('/settings', [AdminController::class, 'settings']);

        // Управление пользователями
        $router->get('/users', [UsersController::class, 'index']);
        $router->get('/users/edit/{id}', [UsersController::class, 'edit']);
        $router->post('/users/update/{id}', [UsersController::class, 'update']);
        $router->post('/users/delete/{id}', [UsersController::class, 'delete']);
        $router->get('/users/create', [UsersController::class, 'create']);
        $router->post('/users/store', [UsersController::class, 'store']);
    }, ['middleware' => [AuthMiddleware::class, new RoleMiddleware('admin')]]);

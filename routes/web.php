<?php

    use App\Core\Response;
    use App\Controllers\AuthController;
    use App\Controllers\Admin\AdminController;
    use App\Controllers\Admin\PluginsController;
    use App\Controllers\Admin\UsersController;
    use App\Middleware\AuthMiddleware;
    use App\Middleware\RoleMiddleware;

    // Главная страница
    $router->get('/', function() {
        return Response::view('home/index', [
            'title' => 'Home - My Application'
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

    // Маршруты управления плагинами
    $router->group('/admin', function() use ($router) {
        $router->get('', [AdminController::class, 'dashboard']);
        $router->get('/settings', [AdminController::class, 'settings']);

        // Управление пользователями
        $router->get('/users', [UsersController::class, 'index']);
        $router->get('/users/edit/{id}', [UsersController::class, 'edit']);
        $router->post('/users/update/{id}', [UsersController::class, 'update']);
        $router->post('/users/delete/{id}', [UsersController::class, 'delete']);
    }, ['middleware' => [AuthMiddleware::class, new RoleMiddleware('admin')]]);

    // Маршруты управления плагинами
    $router->group('/admin/plugins', function() use ($router) {
        $router->get('', ['App\Controllers\Admin\PluginsController', 'index']);
        $router->get('/activate/{pluginName}', ['App\Controllers\Admin\PluginsController', 'activate']);
        $router->get('/deactivate/{pluginName}', ['App\Controllers\Admin\PluginsController', 'deactivate']);
        $router->get('/details/{pluginName}', ['App\Controllers\Admin\PluginsController', 'details']);
    }, ['middleware' => [AuthMiddleware::class, new RoleMiddleware('admin')]]);
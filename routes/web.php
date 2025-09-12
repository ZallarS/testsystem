<?php

    use App\Core\Response;
    use App\Controllers\AuthController;
    use App\Controllers\Admin\PluginsController;
    use App\Controllers\Admin\UsersController;
    use App\Middleware\AuthMiddleware;
    use App\Middleware\RoleMiddleware;

    // Группа для публичных маршрутов
    $router->group('', function() use ($router) {
        // Главная страница
        $router->get('/', function() {
            return Response::view('home/index', [
                'title' => 'Home - My Application'
            ]);
        });

    });

    // Группа для маршрутов аутентификации
    $router->group('', function() use ($router) {
        $router->get('/login', function() {
            $controller = new AuthController();
            return $controller->login();
        });

        $router->post('/login', function() {
            $controller = new AuthController();
            return $controller->processLogin();
        });

        $router->get('/register', function() {
            $controller = new AuthController();
            return $controller->register();
        });

        $router->post('/register', function() {
            $controller = new AuthController();
            return $controller->processRegister();
        });

        $router->get('/logout', function() {
            $controller = new AuthController();
            return $controller->logout();
        });

    });

    // Маршруты управления плагинами
    $router->group('/admin/plugins', function() use ($router) {
        $router->get('', ['App\Controllers\Admin\PluginsController', 'index']);
        $router->get('/activate/{pluginName}', ['App\Controllers\Admin\PluginsController', 'activate']);
        $router->get('/deactivate/{pluginName}', ['App\Controllers\Admin\PluginsController', 'deactivate']);
        $router->get('/details/{pluginName}', ['App\Controllers\Admin\PluginsController', 'details']);
    }, ['middleware' => [AuthMiddleware::class, new RoleMiddleware('admin')]]);
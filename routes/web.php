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

        // Тестовая страница
        $router->get('/test', function() {
            return 'Тестовая страница';
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

    // Группа для административных маршрутов (только для администраторов)
    $router->group('/admin', function() use ($router) {
        $router->get('', function() {
            return 'Admin dashboard';
        });

        $router->get('/users', ['App\Controllers\Admin\UsersController', 'index']);
        $router->get('/users/edit/{id}', ['App\Controllers\Admin\UsersController', 'edit']);
        $router->post('/users/update/{id}', ['App\Controllers\Admin\UsersController', 'update']);


        $router->get('/plugins', ['App\Controllers\Admin\PluginsController', 'index']);
        $router->get('/plugins/activate/{pluginName}', function($pluginName) {
            $controller = new App\Controllers\Admin\PluginsController();
            return $controller->activate($pluginName);
        });

        $router->get('/plugins/deactivate/{pluginName}', function($pluginName) {
            $controller = new App\Controllers\Admin\PluginsController();
            return $controller->deactivate($pluginName);
        });

    }, ['middleware' => [AuthMiddleware::class, new RoleMiddleware('admin')]]);

    // Группа для маршрутов модераторов
    $router->group('/moderator', function() use ($router) {
        $router->get('', function() {
            return 'Панель модератора';
        });

        $router->get('/content', function() {
            return 'Управление контентом';
        });

    }, ['middleware' => [new AuthMiddleware(), new RoleMiddleware('moderator')]]);

    // Группа для защищенных маршрутов (требует авторизации)
    $router->group('', function() use ($router) {
        $router->get('/profile', function() {
            return Response::view('profile/index', [
                'title' => 'Мой профиль',
                'user' => \App\Core\User::get()
            ]);
        });

        $router->get('/settings', function() {
            return Response::view('settings/index', [
                'title' => 'Настройки'
            ]);
        });

    }, ['middleware' => new AuthMiddleware()]);

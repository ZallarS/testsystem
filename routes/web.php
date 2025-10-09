<?php

    use App\Core\Response;
    use App\Controllers\AuthController;
    use App\Controllers\Admin\AdminController;
    use App\Controllers\Admin\UsersController;
    use App\Middleware\AuthMiddleware;
    use App\Middleware\RoleMiddleware;


// Главная страница
$router->get('/', function() {
    $isLoggedIn = \App\Core\User::isLoggedIn();
    $userName = \App\Core\User::getName();
    $userEmail = \App\Core\User::getEmail();
    $userId = \App\Core\User::getId();
    $userRoles = \App\Core\User::getRoles();
    $isAdmin = \App\Core\User::isAdmin();

    // Минимальная системная информация
    $phpVersion = phpversion();
    $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 1) . ' MB';

    return Response::view('home/index', [
        'title' => 'Главная - My Application',
        'activeMenu' => 'home',
        'isLoggedIn' => $isLoggedIn,
        'userName' => $userName,
        'userEmail' => $userEmail,
        'userId' => $userId,
        'userRoles' => $userRoles,
        'isAdmin' => $isAdmin,
        'phpVersion' => $phpVersion,
        'memoryUsage' => $memoryUsage
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
            return \App\Core\Response::redirect('/login');
        }

        return \App\Core\Response::view('profile/index', [
            'title' => 'Личный кабинет - My Application',
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

<?php

    namespace App\Middleware;

    use App\Core\User;
    use App\Core\Response;

    class PermissionMiddleware {
        private $requiredPermission;

        public function __construct($requiredPermission) {
            $this->requiredPermission = $requiredPermission;
        }

        public function handle($next) {
            if (!User::isLoggedIn()) {
                // Пользователь не авторизован
                return Response::redirect('/login');
            }

            if (!User::can($this->requiredPermission)) {
                // У пользователя недостаточно прав
                return Response::make('Access denied. Insufficient permissions.', 403);
            }

            return $next();
        }
    }
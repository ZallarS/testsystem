<?php

    namespace App\Middleware;

    use App\Core\User;
    use App\Core\Response;

    class RoleMiddleware {
        private $requiredRoles;

        public function __construct($roles = []) {
            $this->requiredRoles = is_array($roles) ? $roles : [$roles];
        }

        public function handle($next) {
            $user = \App\Core\User::get();

            // Отладочная информация
            error_log("User roles: " . print_r($user['roles'] ?? [], true));
            error_log("Required roles: " . print_r($this->requiredRoles, true));

            if (empty($this->requiredRoles)) {
                return $next();
            }

            foreach ($this->requiredRoles as $role) {
                if (\App\Core\User::hasRole($role)) {
                    return $next();
                }
            }

            return \App\Core\Response::make('Access denied. Insufficient permissions.', 403);
        }
    }
<?php

    namespace App\Middleware;

    use App\Core\User;
    use App\Core\Response;

    class AuthMiddleware {
        public function handle($next) {
            if (!User::isLoggedIn()) {
                return Response::redirect('/login');
            }
            return $next();
        }
    }
<?php

    namespace App\Middleware;

    use App\Core\Session;

    class SessionMiddleware
    {
        public function handle($next)
        {
            // Автоматически запускаем сессию для каждого HTTP-запроса
            if (php_sapi_name() !== 'cli') {
                Session::start();
            }

            return $next();
        }
    }
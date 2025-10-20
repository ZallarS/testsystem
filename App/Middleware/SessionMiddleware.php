<?php

    namespace App\Middleware;

    use App\Core\Session;

    class SessionMiddleware
    {
        public function handle($next)
        {
            // Просто запускаем сессию - PHP автоматически сохранит ее в конце запроса
            Session::start();

            $response = $next();

            return $response;
        }
    }
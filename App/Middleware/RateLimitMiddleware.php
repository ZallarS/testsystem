<?php

    namespace App\Middleware;

    use App\Core\Response;
    use App\Core\Session;

    class RateLimitMiddleware
    {
        private $maxAttempts;
        private $timeWindow;

        public function __construct($maxAttempts = 5, $timeWindow = 300)
        {
            $this->maxAttempts = $maxAttempts;
            $this->timeWindow = $timeWindow;
        }

        public function handle($next)
        {
            $key = 'rate_limit_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_URI']);
            $now = time();

            $attempts = Session::get($key, []);

            // Удаляем старые попытки
            $attempts = array_filter($attempts, function($time) use ($now) {
                return $time > $now - $this->timeWindow;
            });

            // Проверяем, не превышен ли лимит
            if (count($attempts) >= $this->maxAttempts) {
                return Response::make('Too many requests', 429);
            }

            // Добавляем текущую попытку
            $attempts[] = $now;
            Session::set($key, $attempts);

            return $next();
        }
    }
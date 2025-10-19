<?php

    namespace App\Middleware;

    use App\Core\Response;
    use App\Core\Cache;

    class LoginRateLimitMiddleware
    {
        private $maxAttempts;
        private $timeWindow;
        private $blockDuration;

        public function __construct($maxAttempts = 5, $timeWindow = 900, $blockDuration = 1800)
        {
            $this->maxAttempts = $maxAttempts;
            $this->timeWindow = $timeWindow;
            $this->blockDuration = $blockDuration;
        }

        public function handle($next)
        {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $email = $_POST['email'] ?? '';

            $ipKey = "login_attempts_ip_{$ip}";
            $emailKey = "login_attempts_email_" . md5($email);

            // Проверяем блокировку по IP
            if ($this->isBlocked($ipKey)) {
                return Response::make('Too many login attempts. Please try again later.', 429);
            }

            // Проверяем блокировку по email
            if ($email && $this->isBlocked($emailKey)) {
                return Response::make('Too many login attempts for this email. Please try again later.', 429);
            }

            $result = $next();

            // Если логин неудачный, увеличиваем счетчик
            if ($result->getStatusCode() !== 200) {
                $this->incrementAttempts($ipKey, $emailKey);
            } else {
                // Успешный логин - сбрасываем счетчики
                $this->resetAttempts($ipKey, $emailKey);
            }

            return $result;
        }

        private function isBlocked($key)
        {
            $blockKey = "blocked_{$key}";
            return Cache::get($blockKey) !== null;
        }

        private function incrementAttempts($ipKey, $emailKey)
        {
            $ipAttempts = Cache::get($ipKey) ?? 0;
            $emailAttempts = Cache::get($emailKey) ?? 0;

            $ipAttempts++;
            $emailAttempts++;

            Cache::set($ipKey, $ipAttempts, $this->timeWindow);
            Cache::set($emailKey, $emailAttempts, $this->timeWindow);

            // Блокируем при превышении лимита
            if ($ipAttempts >= $this->maxAttempts) {
                Cache::set("blocked_{$ipKey}", true, $this->blockDuration);
            }

            if ($emailAttempts >= $this->maxAttempts) {
                Cache::set("blocked_{$emailKey}", true, $this->blockDuration);
            }
        }

        private function resetAttempts($ipKey, $emailKey)
        {
            Cache::set($ipKey, 0, $this->timeWindow);
            Cache::set($emailKey, 0, $this->timeWindow);
        }
    }
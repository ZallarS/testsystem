<?php

    namespace App\Core;

    class RateLimiter
    {
        private $storage;
        private $maxAttempts;
        private $decayMinutes;

        public function __construct($maxAttempts = 60, $decayMinutes = 1)
        {
            $this->storage = new Cache();
            $this->maxAttempts = $maxAttempts;
            $this->decayMinutes = $decayMinutes;
        }

        public function attempt($key, \Closure $callback)
        {
            if ($this->tooManyAttempts($key)) {
                throw new \Exception('Too many attempts');
            }

            $result = $callback();

            if ($result === false) {
                $this->hit($key);
                return false;
            }

            $this->clear($key);
            return true;
        }

        public function tooManyAttempts($key)
        {
            $key = $this->cleanKey($key);
            $attempts = $this->storage->get($key, 0);

            return $attempts >= $this->maxAttempts;
        }

        public function hit($key)
        {
            $key = $this->cleanKey($key);
            $attempts = $this->storage->get($key, 0);
            $attempts++;

            $this->storage->set($key, $attempts, $this->decayMinutes * 60);

            return $attempts;
        }

        public function remaining($key)
        {
            $key = $this->cleanKey($key);
            $attempts = $this->storage->get($key, 0);

            return max(0, $this->maxAttempts - $attempts);
        }

        public function clear($key)
        {
            $key = $this->cleanKey($key);
            $this->storage->delete($key);
        }

        public function availableIn($key)
        {
            $key = $this->cleanKey($key);
            $attempts = $this->storage->get($key, 0);

            if ($attempts < $this->maxAttempts) {
                return 0;
            }

            // В реальной реализации нужно получить время истечения
            return 60; // секунд
        }

        private function cleanKey($key)
        {
            return 'rate_limit_' . md5($key);
        }

        public static function forLogin($ip, $email)
        {
            $limiter = new self(5, 15); // 5 попыток за 15 минут для логина
            $key = "login_{$ip}_{$email}";

            return $limiter;
        }

        public static function forApi($apiKey)
        {
            $limiter = new self(100, 60); // 100 запросов в час для API
            $key = "api_{$apiKey}";

            return $limiter;
        }
    }
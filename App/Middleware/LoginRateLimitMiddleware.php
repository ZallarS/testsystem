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

            // Normalize email for consistency
            $email = strtolower(trim($email));

            $ipKey = "login_attempts_ip_" . hash('sha256', $ip);
            $emailKey = "login_attempts_email_" . hash('sha256', $email);

            // Check if blocked
            if ($this->isBlocked($ipKey) || ($email && $this->isBlocked($emailKey))) {
                // Log the attempt
                error_log("Blocked login attempt from IP: $ip, Email: $email");
                return Response::make('Too many login attempts. Please try again later.', 429);
            }

            $result = $next();

            // If login failed, increment counters
            if ($result->getStatusCode() !== 200) {
                $this->incrementAttempts($ipKey, $emailKey);
            } else {
                // Successful login - reset counters
                $this->resetAttempts($ipKey, $emailKey);
            }

            return $result;
        }

        private function incrementAttempts($ipKey, $emailKey)
        {
            $ipAttempts = (int)(Cache::get($ipKey) ?? 0);
            $emailAttempts = (int)(Cache::get($emailKey) ?? 0);

            $ipAttempts++;
            $emailAttempts++;

            Cache::set($ipKey, $ipAttempts, $this->timeWindow);
            if (!empty($emailKey)) {
                Cache::set($emailKey, $emailAttempts, $this->timeWindow);
            }

            // Block if exceeded attempts
            if ($ipAttempts >= $this->maxAttempts) {
                Cache::set("blocked_{$ipKey}", true, $this->blockDuration);
            }

            if ($emailAttempts >= $this->maxAttempts) {
                Cache::set("blocked_{$emailKey}", true, $this->blockDuration);
            }
        }
    }
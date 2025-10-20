<?php

    namespace App\Middleware;

    use App\Core\RateLimiter;
    use App\Core\Response;

    class LoginRateLimitMiddleware
    {
        public function handle($request, $next)
        {
            $ip = $request->getClientIp();
            $email = $request->post('email', '');

            $limiter = RateLimiter::forLogin($ip, $email);

            if ($limiter->tooManyAttempts("login_{$ip}_{$email}")) {
                return Response::json([
                    'error' => 'Too many login attempts. Please try again later.'
                ], 429);
            }

            $response = $next($request);

            // Если логин неудачный, увеличиваем счетчик
            if ($response->getStatusCode() !== 200) {
                $limiter->hit("login_{$ip}_{$email}");
            } else {
                $limiter->clear("login_{$ip}_{$email}");
            }

            return $response;
        }
    }
<?php

    namespace App\Middleware;

    use App\Core\RateLimiter;
    use App\Core\Response;

    class RateLimitMiddleware
    {
        private $maxAttempts;
        private $decayMinutes;

        public function __construct($maxAttempts = 60, $decayMinutes = 1)
        {
            $this->maxAttempts = $maxAttempts;
            $this->decayMinutes = $decayMinutes;
        }

        public function handle($request, $next)
        {
            $key = $this->resolveRequestSignature($request);
            $limiter = new RateLimiter($this->maxAttempts, $this->decayMinutes);

            if ($limiter->tooManyAttempts($key)) {
                return $this->buildResponse($limiter, $key);
            }

            $limiter->hit($key);

            $response = $next($request);

            // Добавляем заголовки с информацией о лимитах
            return $this->addHeaders(
                $response,
                $limiter->remaining($key),
                $limiter->availableIn($key)
            );
        }

        protected function resolveRequestSignature($request)
        {
            return sha1(
                $request->getClientIp() .
                '|' . $request->getPathInfo() .
                '|' . $request->getMethod()
            );
        }

        protected function buildResponse($limiter, $key)
        {
            $retryAfter = $limiter->availableIn($key);

            return Response::json([
                'error' => 'Too Many Requests',
                'retry_after' => $retryAfter
            ], 429)->withHeader('Retry-After', $retryAfter);
        }

        protected function addHeaders($response, $remaining, $retryAfter)
        {
            return $response
                ->withHeader('X-RateLimit-Limit', $this->maxAttempts)
                ->withHeader('X-RateLimit-Remaining', $remaining)
                ->withHeader('X-RateLimit-Reset', time() + $retryAfter);
        }
    }
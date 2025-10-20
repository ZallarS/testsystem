<?php

    namespace App\Core;

    class Request
    {
        private $get;
        private $post;
        private $server;
        private $cookies;
        private $files;
        private $headers;

        public function __construct(array $get = [], array $post = [], array $server = [], array $cookies = [], array $files = [])
        {
            $this->get = $get;
            $this->post = $post;
            $this->server = $server;
            $this->cookies = $cookies;
            $this->files = $files;
            $this->headers = $this->extractHeaders($server);
        }

        public static function createFromGlobals()
        {
            return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
        }

        public function getMethod()
        {
            return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        }

        public function getPathInfo()
        {
            $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);

            // Remove trailing slash
            if ($path !== '/' && substr($path, -1) === '/') {
                $path = substr($path, 0, -1);
            }

            return $path;
        }

        public function get($key, $default = null)
        {
            return $this->get[$key] ?? $default;
        }

        public function post($key, $default = null)
        {
            return $this->post[$key] ?? $default;
        }

        public function input($key, $default = null)
        {
            return $this->post($key, $this->get($key, $default));
        }

        public function all()
        {
            return array_merge($this->get, $this->post);
        }

        public function header($key, $default = null)
        {
            return $this->headers[$key] ?? $default;
        }

        public function isSecure()
        {
            return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ||
                (!empty($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($this->server['HTTP_X_FORWARDED_SSL']) && $this->server['HTTP_X_FORWARDED_SSL'] === 'on') ||
                (isset($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] == 443);
        }

        public function getClientIp()
        {
            return $this->server['REMOTE_ADDR'] ?? 'unknown';
        }

        public function getUserAgent()
        {
            return $this->server['HTTP_USER_AGENT'] ?? '';
        }

        private function extractHeaders(array $server)
        {
            $headers = [];

            foreach ($server as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headers[str_replace('_', '-', substr($key, 5))] = $value;
                }
            }

            return $headers;
        }
    }
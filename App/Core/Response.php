<?php

    namespace App\Core;

    class Response {
        private $content;
        private $statusCode;
        private $headers;

        public function __construct($content = '', $statusCode = 200, $headers = []) {
            $this->content = $content;
            $this->statusCode = $statusCode;
            $this->headers = $headers;
        }

        public static function make($content = '', $statusCode = 200, $headers = []) {
            return new self($content, $statusCode, $headers);
        }

        public static function json($data, $statusCode = 200, $headers = []) {
            $headers['Content-Type'] = 'application/json';
            return new self(json_encode($data), $statusCode, $headers);
        }

        public static function view($viewPath, $data = [], $statusCode = 200, $headers = []) {
            extract($data);
            ob_start();
            include VIEWS_PATH . $viewPath . '.php';
            $content = ob_get_clean();

            return new self($content, $statusCode, $headers);
        }

        public static function redirect($url, $statusCode = 302) {
            header("Location: $url", true, $statusCode);
            exit;
        }

        public function send() {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }

            echo $this->content;
        }
    }
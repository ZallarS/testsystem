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

        public static function view($viewPath, $data = [], $statusCode = 200, $headers = [])
        {
            // Автоматически экранируем все строковые данные
            $data = array_map(function($item) {
                if (is_string($item)) {
                    return htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                }
                if (is_array($item)) {
                    return array_map(function($subItem) {
                        return is_string($subItem) ? htmlspecialchars($subItem, ENT_QUOTES, 'UTF-8') : $subItem;
                    }, $item);
                }
                return $item;
            }, $data);

            extract($data);
            ob_start();
            include VIEWS_PATH . $viewPath . '.php';
            $content = ob_get_clean();

            return new self($content, $statusCode, $headers);
        }

        public static function redirect($url, $statusCode = 303)
        {
            // Для CLI-режима просто выводим сообщение
            if (php_sapi_name() === 'cli') {
                echo "Redirect to: $url\n";
                return;
            }

            // Очищаем буфер вывода
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Устанавливаем абсолютный URL, если передан относительный
            if (strpos($url, 'http') !== 0 && strpos($url, '://') === false) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
                $url = $protocol . $host . $url;
            }

            // Устанавливаем заголовок Location
            header("Location: $url", true, $statusCode);

            // Добавляем HTML для браузеров, которые могут игнорировать заголовки
            echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0; url=' . htmlspecialchars($url) . '"></head><body><p>Redirecting to <a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a></p></body></html>';

            exit;
        }

        public function send() {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }

            echo $this->content;
        }

        public static function jsRedirect($url, $message = null, $delay = 0)
        {
            // Устанавливаем абсолютный URL, если передан относительный
            if (strpos($url, 'http') !== 0 && strpos($url, '://') === false) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
                $url = $protocol . $host . $url;
            }

            $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
        <meta http-equiv="refresh" content="' . $delay . '; url=' . htmlspecialchars($url) . '">
    </head>
    <body>
        <div style="text-align: center; margin-top: 50px;">';

            if ($message) {
                $html .= '<p>' . htmlspecialchars($message) . '</p>';
            }

            $html .= '<p>Redirecting to <a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a></p>
            <div class="loading-spinner" style="margin: 20px auto; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "' . $url . '";
            }, ' . ($delay * 1000) . ');
        </script>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </body>
    </html>';

            return new self($html);
        }
    }
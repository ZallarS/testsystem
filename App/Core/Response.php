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

        public static function json($data, $statusCode = 200, $headers = [])
        {
            $headers['Content-Type'] = 'application/json; charset=utf-8';

            $safeData = self::sanitizeJsonData($data);

            return new self(json_encode($safeData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), $statusCode, $headers);
        }

        private static function sanitizeJsonData($data)
        {
            if (is_array($data)) {
                return array_map([self::class, 'sanitizeJsonData'], $data);
            }

            if (is_string($data)) {
                // Экранируем для безопасного встраивания в JavaScript
                return htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
            }

            return $data;
        }

        public static function view($viewPath, $data = [], $statusCode = 200, $headers = [])
        {
            // Устанавливаем правильный Content-Type
            $headers['Content-Type'] = 'text/html; charset=utf-8';

            $renderer = new ViewRenderer();

            try {
                // Рендерим основной контент
                $content = $renderer->render($viewPath, $data);

                // Проверяем существование layout
                $layoutFile = VIEWS_PATH . 'layout/main.php';
                if (file_exists($layoutFile)) {
                    // Рендерим layout с контентом
                    $layoutData = array_merge($data, ['content' => $content]);
                    $finalContent = $renderer->render('layout/main', $layoutData);
                } else {
                    $finalContent = $content;
                }

                return new self($finalContent, $statusCode, $headers);
            } catch (\Exception $e) {
                error_log("View rendering error: " . $e->getMessage());
                return new self("Error rendering view", 500, $headers);
            }
        }

        public static function redirect($url, $statusCode = 303)
        {
            // Проверяем, является ли URL относительным
            if (strpos($url, '://') === false && $url[0] !== '/') {
                $url = '/' . $url;
            }

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
            // Устанавливаем статус код
            http_response_code($this->statusCode);

            // Устанавливаем заголовки
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }

            // Выводим контент
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
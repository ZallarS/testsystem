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
            $headers['X-Content-Type-Options'] = 'nosniff';

            // Добавляем security headers
            self::addSecurityHeaders($headers);

            $safeData = self::sanitizeJsonData($data);

            // Дополнительная проверка на валидность JSON
            $json = json_encode($safeData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

            if ($json === false) {
                throw new \RuntimeException('Failed to encode JSON data');
            }

            return new self($json, $statusCode, $headers);
        }

        private static function sanitizeJsonData($data)
        {
            if (is_array($data)) {
                return array_map([self::class, 'sanitizeJsonData'], $data);
            }

            if (is_object($data)) {
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = self::sanitizeJsonData($value);
                }
                return $result;
            }

            if (is_string($data)) {
                // Экранируем для безопасного JSON с учетом JavaScript контекста
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            return $data;
        }

        public static function view($viewPath, $data = [], $statusCode = 200, $headers = [])
        {
            $headers['Content-Type'] = 'text/html; charset=utf-8';

            // Security headers
            self::addSecurityHeaders($headers);

            $renderer = new ViewRenderer();

            try {
                $content = $renderer->render($viewPath, $data);

                $layoutFile = VIEWS_PATH . 'layout/main.php';
                if (file_exists($layoutFile)) {
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
            http_response_code($this->statusCode);

            // Add security headers to all responses
            $this->addSecurityHeadersToResponse();

            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }

            echo $this->content;
        }

        private function addSecurityHeadersToResponse()
        {
            $securityHeaders = [
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'X-Content-Type-Options' => 'nosniff',
                'Referrer-Policy' => 'strict-origin-when-cross-origin',
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            ];

            foreach ($securityHeaders as $key => $value) {
                if (!isset($this->headers[$key])) {
                    header("$key: $value");
                }
            }
        }

        public function withHeader($name, $value)
        {
            $this->headers[$name] = $value;
            return $this;
        }

        public function withCookie($name, $value, $minutes = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true)
        {
            $expire = $minutes > 0 ? time() + ($minutes * 60) : 0;

            setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
            return $this;
        }

        public function getStatusCode()
        {
            return $this->statusCode;
        }

        public function getContent()
        {
            return $this->content;
        }

        public static function download($filePath, $fileName = null, $headers = [])
        {
            if (!file_exists($filePath)) {
                return new self('File not found', 404);
            }

            $fileName = $fileName ?: basename($filePath);
            $fileSize = filesize($filePath);

            $headers = array_merge([
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => $fileSize,
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ], $headers);

            $content = file_get_contents($filePath);

            return new self($content, 200, $headers);
        }

        public static function noContent()
        {
            return new self('', 204);
        }

        public static function created($data = null)
        {
            return self::json($data, 201);
        }

        public static function accepted($data = null)
        {
            return self::json($data, 202);
        }

        private static function addSecurityHeaders(&$headers = [])
        {
            $securityHeaders = [
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'X-Content-Type-Options' => 'nosniff',
                'Referrer-Policy' => 'strict-origin-when-cross-origin',
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            ];

            foreach ($securityHeaders as $key => $value) {
                if (!isset($headers[$key])) {
                    $headers[$key] = $value;
                }
            }
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
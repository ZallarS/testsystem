<?php

    namespace App\Core;

    class Session
    {
        private static $started = false;

        public static function start()
        {
            if (self::$started) {
                return;
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                self::$started = true;
                return;
            }

            // Базовые настройки безопасности
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_samesite', 'Lax');

            session_name('TESTSYSTEM_SID');

            $cookieParams = [
                'lifetime' => 86400,
                'path' => '/',
                'domain' => self::getDomain(),
                'secure' => self::isSecure(),
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            session_set_cookie_params($cookieParams);

            // Development настройки
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                ini_set('session.cookie_secure', '0');
            }

            if (!session_start()) {
                throw new \RuntimeException('Failed to start session');
            }

            self::$started = true;

            // Инициализируем базовые данные сессии если их нет
            if (empty($_SESSION['_initialized'])) {
                $_SESSION['_initialized'] = true;
                $_SESSION['_created'] = time();
            }

            error_log("Session started: " . session_id() . ", data: " . count($_SESSION) . " keys");
        }

        public static function regenerate($deleteOld = true)
        {
            if (!self::$started) {
                self::start();
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                $result = session_regenerate_id($deleteOld);
                if ($result) {
                    error_log("Session regenerated with new ID: " . session_id());
                } else {
                    error_log("Session regeneration failed");
                }
                return $result;
            }

            return false;
        }

        public static function get($key, $default = null)
        {
            self::start();
            return $_SESSION[$key] ?? $default;
        }

        public static function set($key, $value)
        {
            self::start();
            $_SESSION[$key] = $value;
        }

        public static function remove($key)
        {
            self::start();
            unset($_SESSION[$key]);
        }

        public static function destroy()
        {
            error_log("Session::destroy() called");

            if (self::$started) {
                // Логируем данные сессии перед уничтожением
                error_log("Session data before destroy: " . print_r($_SESSION, true));

                // Очищаем данные сессии
                $_SESSION = [];

                // Удаляем cookie сессии
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params["path"],
                        $params["domain"],
                        $params["secure"],
                        $params["httponly"]
                    );
                    error_log("Session cookie deleted");
                }

                // Уничтожаем сессию
                if (session_destroy()) {
                    error_log("Session destroyed successfully");
                } else {
                    error_log("Session destruction failed");
                }
            } else {
                error_log("Session not started, cannot destroy");
            }

            self::$started = false;
            error_log("Session::destroy() completed");
        }

        public static function id()
        {
            return session_id();
        }

        private static function getDomain()
        {
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

            // Удаляем порт
            if (strpos($host, ':') !== false) {
                $host = substr($host, 0, strpos($host, ':'));
            }

            // Для localhost/IP оставляем как есть
            if ($host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
                return $host;
            }

            // Для доменов берем основной домен
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $host = implode('.', array_slice($parts, -2));
            }

            return $host;
        }

        private static function isSecure()
        {
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        }
    }
<?php

    namespace App\Core;

    class Session
    {
        private static $cliSessionData = [];
        private static $cliMode = false;
        private static $sessionStarted = false;

        public static function start()
        {
            if (self::$sessionStarted) {
                return;
            }

            self::$sessionStarted = true;
            self::$cliMode = (php_sapi_name() === 'cli');

            if (self::$cliMode) {
                if (empty(self::$cliSessionData)) {
                    self::$cliSessionData = ['created' => time()];
                }
                return;
            }

            // Security headers
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');

            if (session_status() === PHP_SESSION_ACTIVE) {
                if (session_name() !== 'TESTSYSTEM_SID') {
                    session_write_close();
                } else {
                    self::initializeSessionData();
                    return;
                }
            }

            session_name('TESTSYSTEM_SID');

            $cookieParams = [
                'lifetime' => 86400,
                'path' => '/',
                'domain' => self::getDomain(),
                'secure' => self::isSecure(),
                'httponly' => true,
                'samesite' => 'Strict' // Changed from Lax to Strict
            ];

            session_set_cookie_params($cookieParams);

            // Enhanced security settings
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', self::isSecure() ? '1' : '0');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 3600);
            ini_set('session.cookie_lifetime', 86400);

            // Prevent session fixation
            if (!session_start()) {
                throw new \RuntimeException('Failed to start session');
            }

            self::initializeSessionData();
            self::validateSession();
        }

        private static function initializeSessionData()
        {
            if (empty($_SESSION['created']) || empty($_SESSION['regenerated_at'])) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['ip_hash'] = self::hashIp($_SERVER['REMOTE_ADDR'] ?? '');
                $_SESSION['regenerated_at'] = time();
            }
        }

        private static function validateSession()
        {
            if (!isset($_SESSION['user_agent']) || !isset($_SESSION['ip_hash'])) {
                self::regenerate(true);
                return;
            }

            $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $currentIpHash = self::hashIp($_SERVER['REMOTE_ADDR'] ?? '');

            // Строгая проверка User-Agent
            if (!hash_equals($_SESSION['user_agent'], $currentUserAgent)) {
                self::destroy();
                throw new \RuntimeException('Session user agent mismatch');
            }

            // Усиленная проверка IP с возможностью настройки строгости
            if (!self::validateIp($_SESSION['ip_hash'], $currentIpHash)) {
                self::destroy();
                throw new \RuntimeException('Session IP validation failed');
            }

            // Частая регенерация session ID
            if (time() - ($_SESSION['regenerated_at'] ?? 0) > 300) { // 5 минут вместо 15
                self::regenerate(true);
            }
        }

        private static function validateIp($storedIpHash, $currentIpHash)
        {
            // В production - строгая проверка полного IP
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                return hash_equals($storedIpHash, $currentIpHash);
            }

            // В development - проверка по подсети для удобства
            $storedParts = explode('.', $storedIpHash);
            $currentParts = explode('.', $currentIpHash);

            return count($storedParts) >= 3 && count($currentParts) >= 3 &&
                $storedParts[0] === $currentParts[0] &&
                $storedParts[1] === $currentParts[1] &&
                $storedParts[2] === $currentParts[2];
        }

        // ДОБАВЛЯЕМ метод для безопасного получения всех данных сессии
        public static function getAll()
        {
            self::start();

            if (self::$cliMode) {
                return self::$cliSessionData;
            }

            // Возвращаем копию для безопасности
            return $_SESSION ? array_merge([], $_SESSION) : [];
        }

        private static function isSecure()
        {
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        }

        private static function getAppSecret()
        {
            // Безопасное получение секрета без зависимостей
            $secret = $_ENV['APP_SECRET'] ?? 'default-secret-key-change-in-production';

            // Если секрет по умолчанию, логируем предупреждение
            if ($secret === 'default-secret-key-change-in-production') {
                error_log('SECURITY WARNING: Using default app secret. Change APP_SECRET in .env file!');
            }

            return $secret;
        }

        private static function hashIp($ip)
        {
            return hash('sha256', $ip . self::getAppSecret());
        }

        private static function getDomain()
        {
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

            // Удаляем порт из домена для куки
            if (strpos($host, ':') !== false) {
                $host = substr($host, 0, strpos($host, ':'));
            }

            // Для localhost оставляем как есть
            if ($host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
                return $host;
            }

            // Для реальных доменов оставляем основной домен
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $host = implode('.', array_slice($parts, -2));
            }

            return $host;
        }

        public static function get($key, $default = null)
        {
            self::start();

            if (self::$cliMode) {
                return self::$cliSessionData[$key] ?? $default;
            }

            return $_SESSION[$key] ?? $default;
        }

        public static function set($key, $value)
        {
            self::start();

            if (self::$cliMode) {
                self::$cliSessionData[$key] = $value;
                return;
            }

            $_SESSION[$key] = $value;
        }

        public static function remove($key)
        {
            self::start();

            if (self::$cliMode) {
                unset(self::$cliSessionData[$key]);
                return;
            }

            unset($_SESSION[$key]);
        }

        public static function destroy()
        {
            if (self::$cliMode) {
                self::$cliSessionData = [];
                self::$sessionStarted = false;
                return;
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                // Очищаем все данные сессии
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
                }

                session_destroy();
            }

            self::$sessionStarted = false;
        }

        public static function id()
        {
            if (self::$cliMode) {
                return 'cli-session-' . md5(serialize(self::$cliSessionData));
            }

            return session_id();
        }

        public static function status()
        {
            if (self::$cliMode) {
                return PHP_SESSION_ACTIVE;
            }

            return session_status();
        }

        public static function isCliMode()
        {
            return self::$cliMode;
        }

        public static function regenerate($deleteOld = true)
        {
            if (!self::$cliMode && session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id($deleteOld);
                $_SESSION['regenerated_at'] = time();
                $_SESSION['ip_hash'] = self::hashIp($_SERVER['REMOTE_ADDR'] ?? '');
            }
        }

        public static function regenerateOnAuthChange()
        {
            if (!self::$cliMode && session_status() === PHP_SESSION_ACTIVE) {
                // Создаем новый идентификатор сессии
                session_regenerate_id(true);

                // Переносим данные в новую сессию
                $oldSessionData = $_SESSION;
                $_SESSION = [];
                session_regenerate_id(true);
                $_SESSION = $oldSessionData;

                $_SESSION['regenerated_at'] = time();
                $_SESSION['auth_level'] = self::calculateAuthLevel();

                // Обновляем метаданные безопасности
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['ip_hash'] = self::hashIp($_SERVER['REMOTE_ADDR'] ?? '');
            }
        }

        private static function calculateAuthLevel()
        {
            $user = \App\Core\User::get();
            if (!$user) {
                return 'guest';
            }

            if (in_array('admin', $user['roles'] ?? [])) {
                return 'admin';
            }

            return 'user';
        }

        public static function validateAuthLevel()
        {
            $currentLevel = self::calculateAuthLevel();
            $storedLevel = $_SESSION['auth_level'] ?? 'guest';

            if ($currentLevel !== $storedLevel) {
                // Уровень аутентификации изменился - регенерируем сессию
                self::regenerateOnAuthChange();
                return false;
            }

            return true;
        }

        public static function secureSessionStart()
        {
            // Prevent session fixation
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', self::isSecure() ? '1' : '0');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_samesite', 'Strict');

            // Session configuration
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
            ini_set('session.cookie_lifetime', 0); // Until browser closes

            // Additional security headers
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => self::getDomain(),
                'secure' => self::isSecure(),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        public static function validateSessionSecurity()
        {
            // Validate session regeneration frequency
            $lastRegeneration = $_SESSION['regenerated_at'] ?? 0;
            if (time() - $lastRegeneration > 300) { // 5 minutes
                self::regenerate(true);
            }

            // Validate session inactivity
            $lastActivity = $_SESSION['last_activity'] ?? 0;
            if (time() - $lastActivity > 1800) { // 30 minutes
                self::destroy();
                throw new \RuntimeException('Session expired due to inactivity');
            }

            // Update last activity
            $_SESSION['last_activity'] = time();
        }

        public static function generateSessionFingerprint()
        {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

            return hash('sha256', $userAgent . $ip . $acceptLanguage . self::getAppSecret());
        }

        public static function validateSessionFingerprint()
        {
            $currentFingerprint = self::generateSessionFingerprint();
            $storedFingerprint = $_SESSION['fingerprint'] ?? '';

            if (!hash_equals($storedFingerprint, $currentFingerprint)) {
                self::destroy();
                throw new \RuntimeException('Session fingerprint validation failed');
            }
        }
    }
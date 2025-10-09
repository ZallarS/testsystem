<?php

namespace App\Core;

class Session
{
    private static $cliSessionData = [];
    private static $cliMode = false;

    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Определяем, работаем ли мы в CLI-режиме
            self::$cliMode = (php_sapi_name() === 'cli');

            if (self::$cliMode) {
                // В CLI-режиме используем массив вместо реальной сессии
                if (empty(self::$cliSessionData)) {
                    self::$cliSessionData = ['created' => time()];
                }
                return;
            }

            // Увеличиваем безопасность cookie
            session_name('TESTSYSTEM_SID');
            $domain = self::getDomain();
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            $cookieParams = [
                'lifetime' => 86400, // 24 часа
                'path' => '/',
                'domain' => $domain,
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Strict' // Изменено на Strict для большей безопасности
            ];

            session_set_cookie_params($cookieParams);

            // Устанавливаем путь сохранения сессий
            $sessionPath = sys_get_temp_dir() . '/testsystem_sessions/';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0755, true);
            }
            ini_set('session.save_path', $sessionPath);

            // Увеличиваем безопасность сессий
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $isSecure ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_trans_sid', 0);

            // Запускаем сессию
            if (!session_start()) {
                throw new \RuntimeException('Failed to start session');
            }

            // Защита от фиксации сессии - всегда регенерируем ID после создания
            if (empty($_SESSION['created'])) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
            } else {
                // Проверяем User-Agent и IP
                $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';

                if ($_SESSION['user_agent'] !== $currentUserAgent || $_SESSION['ip'] !== $currentIp) {
                    session_unset();
                    session_destroy();
                    self::start(); // Перезапускаем сессию
                    return;
                }

                // Регенерируем ID каждые 15 минут для большей безопасности
                if (time() - $_SESSION['created'] > 900) {
                    session_regenerate_id(true);
                    $_SESSION['created'] = time();
                }
            }
        }
    }

    public static function regenerate()
    {
        if (!self::$cliMode && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function get($key, $default = null)
    {
        self::start();

        if (self::$cliMode) {
            return self::$cliSessionData[$key] ?? $default;
        }

        return $_SESSION[$key] ?? $default;
    }

    private static function getDomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Удаляем порт из домена для куки
        if (strpos($host, ':') !== false) {
            $host = substr($host, 0, strpos($host, ':'));
        }

        // Для localhost оставляем как есть, иначе удаляем www и субдомены
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            // Оставляем только основной домен и поддомен верхнего уровня
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $host = implode('.', array_slice($parts, -2));
            }
        }

        return $host;
    }

    public static function set($key, $value)
    {
        self::start();

        if (self::$cliMode) {
            self::$cliSessionData[$key] = $value;
            return;
        }

        // Для HTTP-режима убедимся, что сессия активна
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$key] = $value;

            // Немедленно сохраняем изменения в сессии
            session_write_close();

            // И сразу же открываем сессию снова для последующих операций
            session_start();
        } else {
            error_log("Cannot set session value: session is not active");
        }
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
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            session_write_close();

            // Удаляем cookie сессии
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        }
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
}
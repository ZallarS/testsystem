<?php

namespace App\Core;

class CSRF
{
    private static $tokenLength = 32;
    private static $tokenLifetime = 3600; // 1 час
    private static $storageDriver = 'session'; // или 'redis', 'database'

    public static function setStorageDriver($driver)
    {
        self::$storageDriver = $driver;
    }



    public static function generateToken()
    {
        if (empty($_SESSION['csrf_secret'])) {
            $_SESSION['csrf_secret'] = bin2hex(random_bytes(32));
        }

        $token = bin2hex(random_bytes(32));
        $expires = time() + 3600; // 1 час

        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        // Ограничиваем количество токенов
        if (count($_SESSION['csrf_tokens']) > 10) {
            array_shift($_SESSION['csrf_tokens']);
        }

        $tokenData = [
            'token' => $token,
            'expires' => $expires,
            'created' => time()
        ];

        $_SESSION['csrf_tokens'][$token] = $tokenData;

        return $token;
    }

    private static function storeToken($tokenId, $tokenData)
    {
        switch (self::$storageDriver) {
            case 'redis':
                // Реализация для Redis
                break;
            case 'database':
                // Реализация для БД
                break;
            case 'session':
            default:
                $_SESSION['csrf_tokens'][$tokenId] = $tokenData;
        }
    }

    public static function validateToken($token)
    {
        if (empty($token) || !is_string($token)) {
            throw new \Exception("Empty or invalid CSRF token");
        }

        if (!isset($_SESSION['csrf_tokens'][$token])) {
            throw new \Exception("CSRF token not found");
        }

        $tokenData = $_SESSION['csrf_tokens'][$token];

        // Проверяем срок действия
        if (time() > $tokenData['expires']) {
            unset($_SESSION['csrf_tokens'][$token]);
            throw new \Exception("CSRF token expired");
        }

        // Удаляем использованный токен
        unset($_SESSION['csrf_tokens'][$token]);

        return true;
    }

    private static function cleanExpiredTokens()
    {
        if (empty($_SESSION['csrf_tokens'])) {
            return;
        }

        $currentTime = time();
        foreach ($_SESSION['csrf_tokens'] as $tokenId => $tokenData) {
            if (($currentTime - $tokenData['created_at']) > self::$tokenLifetime) {
                unset($_SESSION['csrf_tokens'][$tokenId]);
            }
        }
    }

    public static function getMetaTag()
    {
        $token = self::generateToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function getHeader()
    {
        return self::generateToken();
    }

    private static function getAppSecret()
    {
        $secret = $_ENV['APP_SECRET'] ?? null;
        if (!$secret || $secret === 'your-secret-key-change-this-in-production') {
            throw new \RuntimeException('APP_SECRET is not properly configured');
        }
        return $secret;
    }
}
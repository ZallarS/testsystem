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
        $sessionId = session_id();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (empty($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        // Очищаем старые токены
        self::cleanExpiredTokens();

        $token = bin2hex(random_bytes(self::$tokenLength));
        $tokenId = uniqid('', true);

        $_SESSION['csrf_tokens'][$tokenId] = [
            'token' => $token,
            'created_at' => time(),
            'session_id' => $sessionId,
            'user_agent' => substr(md5($userAgent), 0, 8)
        ];

        // Ограничиваем количество токенов
        if (count($_SESSION['csrf_tokens']) > 10) {
            array_shift($_SESSION['csrf_tokens']);
        }

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

        // Проверяем фортокен (разделенный токен)
        if (strpos($token, '.') !== false) {
            list($tokenValue, $tokenHmac) = explode('.', $token);

            $expectedHmac = hash_hmac('sha256', $tokenValue, $secretKey ?? self::getAppSecret());
            if (!hash_equals($expectedHmac, $tokenHmac)) {
                throw new \Exception("CSRF token integrity check failed");
            }

            $token = $tokenValue;
        }

        if (empty($_SESSION['csrf_tokens'])) {
            throw new \Exception("No CSRF tokens in session");
        }

        $currentTime = time();
        $sessionId = session_id();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        foreach ($_SESSION['csrf_tokens'] as $tokenId => $tokenData) {
            if (!isset($tokenData['token'], $tokenData['created_at'])) {
                unset($_SESSION['csrf_tokens'][$tokenId]);
                continue;
            }

            // Проверяем срок жизни
            if (($currentTime - $tokenData['created_at']) > self::$tokenLifetime) {
                unset($_SESSION['csrf_tokens'][$tokenId]);
                continue;
            }

            // Проверяем сессию и user-agent
            if ($tokenData['session_id'] !== $sessionId) {
                continue;
            }

            $currentUserAgentHash = substr(md5($userAgent), 0, 8);
            if ($tokenData['user_agent'] !== $currentUserAgentHash) {
                continue;
            }

            // Сравниваем токены
            if (hash_equals($tokenData['token'], $token)) {
                // Удаляем использованный токен
                unset($_SESSION['csrf_tokens'][$tokenId]);
                return true;
            }
        }

        throw new \Exception("CSRF token not found or expired");
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
}
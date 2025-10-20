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

        // Use app secret from configuration
        $secret = self::getAppSecret();

        if (empty($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        self::cleanExpiredTokens();

        $token = bin2hex(random_bytes(self::$tokenLength));
        $tokenId = uniqid('', true);

        // Create signed token
        $tokenData = [
            'token' => $token,
            'created_at' => time(),
            'session_id' => $sessionId,
            'user_agent' => substr(hash_hmac('sha256', $userAgent, $secret), 0, 16)
        ];

        // Sign the token
        $signature = hash_hmac('sha256', json_encode($tokenData), $secret);
        $tokenData['signature'] = $signature;

        $_SESSION['csrf_tokens'][$tokenId] = $tokenData;

        if (count($_SESSION['csrf_tokens']) > 10) {
            array_shift($_SESSION['csrf_tokens']);
        }

        // Return signed token
        return $token . '.' . $signature;
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

        // Verify token signature
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new \Exception("Invalid token format");
        }

        list($tokenValue, $tokenSignature) = $parts;
        $secret = self::getAppSecret();

        if (empty($_SESSION['csrf_tokens'])) {
            throw new \Exception("No CSRF tokens in session");
        }

        $currentTime = time();
        $sessionId = session_id();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        foreach ($_SESSION['csrf_tokens'] as $tokenId => $tokenData) {
            if (!isset($tokenData['token'], $tokenData['created_at'], $tokenData['signature'])) {
                unset($_SESSION['csrf_tokens'][$tokenId]);
                continue;
            }

            // Verify signature
            $expectedSignature = hash_hmac('sha256', json_encode([
                'token' => $tokenData['token'],
                'created_at' => $tokenData['created_at'],
                'session_id' => $tokenData['session_id'],
                'user_agent' => $tokenData['user_agent']
            ]), $secret);

            if (!hash_equals($tokenData['signature'], $expectedSignature)) {
                unset($_SESSION['csrf_tokens'][$tokenId]);
                continue;
            }

            // Check expiration
            if (($currentTime - $tokenData['created_at']) > self::$tokenLifetime) {
                unset($_SESSION['csrf_tokens'][$tokenId]);
                continue;
            }

            // Verify session and user-agent
            if ($tokenData['session_id'] !== $sessionId) {
                continue;
            }

            $currentUserAgentHash = substr(hash_hmac('sha256', $userAgent, $secret), 0, 16);
            if ($tokenData['user_agent'] !== $currentUserAgentHash) {
                continue;
            }

            // Compare tokens
            if (hash_equals($tokenData['token'], $tokenValue)) {
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

    private static function getAppSecret()
    {
        $secret = $_ENV['APP_SECRET'] ?? null;
        if (!$secret || $secret === 'your-secret-key-change-this-in-production') {
            throw new \RuntimeException('APP_SECRET is not properly configured');
        }
        return $secret;
    }
}
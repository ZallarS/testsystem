<?php

    namespace App\Core;

    class CSRF
    {
        public static function generateToken()
        {
            Session::start();

            if (!isset($_SESSION['csrf_tokens'])) {
                $_SESSION['csrf_tokens'] = [];
            }

            // Очищаем старые токены
            self::cleanExpiredTokens();

            $token = bin2hex(random_bytes(32));
            $expires = time() + 3600; // 1 час

            $_SESSION['csrf_tokens'][$token] = [
                'expires' => $expires,
                'created' => time()
            ];

            error_log("CSRF Token generated: $token, total tokens: " . count($_SESSION['csrf_tokens']));
            return $token;
        }

        public static function validateToken($token)
        {
            Session::start();

            error_log("CSRF Validation: Looking for token: " . ($token ? substr($token, 0, 10) . "..." : "EMPTY"));
            error_log("CSRF Validation: Available tokens: " . count($_SESSION['csrf_tokens'] ?? []));

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
            error_log("CSRF Validation: SUCCESS");

            return true;
        }

        private static function cleanExpiredTokens()
        {
            if (empty($_SESSION['csrf_tokens'])) {
                return;
            }

            $now = time();
            foreach ($_SESSION['csrf_tokens'] as $token => $data) {
                if ($now > $data['expires']) {
                    unset($_SESSION['csrf_tokens'][$token]);
                }
            }

            // Ограничиваем количество токенов
            if (count($_SESSION['csrf_tokens']) > 20) {
                $_SESSION['csrf_tokens'] = array_slice($_SESSION['csrf_tokens'], -10, 10, true);
            }
        }

        public static function getMetaTag()
        {
            $token = self::generateToken();
            return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
        }
    }
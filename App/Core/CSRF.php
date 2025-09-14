<?php

    namespace App\Core;

    class CSRF
    {
        public static function generateToken()
        {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }

        public static function validateToken($token)
        {
            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                throw new \Exception("CSRF token validation failed");
            }
            return true;
        }

        public static function generateMetaTag()
        {
            $token = self::generateToken();
            return '<meta name="csrf-token" content="' . e($token) . '">';
        }
    }
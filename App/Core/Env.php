<?php

    namespace App\Core;

    class Env {
        private static $vars = [];

        public static function load($filePath) {
            if (!file_exists($filePath)) {
                return false;
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                    self::$vars[$name] = $value;
                }
            }

            return true;
        }

        public static function get($key, $default = null) {
            $value = getenv($key);

            if ($value === false) {
                return $default;
            }

            // Преобразование строковых значений
            switch (strtolower($value)) {
                case 'true':
                    return true;
                case 'false':
                    return false;
                case 'null':
                    return null;
                default:
                    return $value;
            }
        }
    }
<?php

    namespace App\Core;

    class Cache
    {
        private static $driver = 'file';
        private static $config = [];

        public static function get($key, $default = null)
        {
            switch (self::$driver) {
                case 'redis':
                    return self::redisGet($key, $default);
                case 'apc':
                    return self::apcGet($key, $default);
                case 'file':
                default:
                    return self::fileGet($key, $default);
            }
        }

        private static function fileSet($key, $value, $ttl)
        {
            $data = [
                'value' => $value,
                'expires' => time() + $ttl,
                'created' => time()
            ];

            file_put_contents(self::getCachePath($key), serialize($data));
        }

        private static function fileGet($key, $default)
        {
            $file = self::getCachePath($key);
            if (file_exists($file) && time() - filemtime($file) < 3600) {
                $data = unserialize(file_get_contents($file));
                if (isset($data['expires']) && $data['expires'] < time()) {
                    unlink($file);
                    return $default;
                }
                return $data['value'] ?? $default;
            }
            return $default;
        }

        public static function set($key, $value)
        {
            file_put_contents(self::getCachePath($key), serialize($value));
        }

        private static function getCachePath($key)
        {
            return sys_get_temp_dir() . '/app_cache_' . md5($key);
        }
    }
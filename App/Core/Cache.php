<?php

    namespace App\Core;

    class Cache
    {
        public static function get($key)
        {
            $file = self::getCachePath($key);
            if (file_exists($file) && time() - filemtime($file) < 3600) {
                return unserialize(file_get_contents($file));
            }
            return null;
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
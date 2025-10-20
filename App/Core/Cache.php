<?php

    namespace App\Core;

    class Cache
    {
        private static $driver = 'file';
        private static $config = [];
        private static $prefix = 'app_';

        public static function initialize($driver = null, $config = [])
        {
            if ($driver) {
                self::$driver = $driver;
            }
            self::$config = $config;
        }

        public static function get($key, $default = null)
        {
            $key = self::prefixKey($key);

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

        public static function set($key, $value, $ttl = 3600)
        {
            $key = self::prefixKey($key);

            switch (self::$driver) {
                case 'redis':
                    return self::redisSet($key, $value, $ttl);
                case 'apc':
                    return self::apcSet($key, $value, $ttl);
                case 'file':
                default:
                    return self::fileSet($key, $value, $ttl);
            }
        }

        public static function delete($key)
        {
            $key = self::prefixKey($key);

            switch (self::$driver) {
                case 'redis':
                    return self::redisDelete($key);
                case 'apc':
                    return self::apcDelete($key);
                case 'file':
                default:
                    return self::fileDelete($key);
            }
        }

        public static function clear()
        {
            switch (self::$driver) {
                case 'redis':
                    return self::redisClear();
                case 'apc':
                    return self::apcClear();
                case 'file':
                default:
                    return self::fileClear();
            }
        }

        private static function fileSet($key, $value, $ttl)
        {
            $data = [
                'value' => $value,
                'expires' => time() + $ttl,
                'created' => time()
            ];

            $file = self::getCachePath($key);
            $dir = dirname($file);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            return file_put_contents($file, serialize($data), LOCK_EX);
        }

        private static function fileGet($key, $default)
        {
            $file = self::getCachePath($key);

            if (!file_exists($file)) {
                return $default;
            }

            $data = unserialize(file_get_contents($file));

            if (!is_array($data) || !isset($data['expires'])) {
                unlink($file);
                return $default;
            }

            // Check expiration
            if (time() > $data['expires']) {
                unlink($file);
                return $default;
            }

            return $data['value'] ?? $default;
        }

        private static function fileDelete($key)
        {
            $file = self::getCachePath($key);

            if (file_exists($file)) {
                return unlink($file);
            }

            return false;
        }

        private static function fileClear()
        {
            $cacheDir = self::getCacheDir();
            return self::clearDirectory($cacheDir);
        }

        private static function getCachePath($key)
        {
            $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
            return self::getCacheDir() . DIRECTORY_SEPARATOR . $safeKey . '.cache';
        }

        private static function getCacheDir()
        {
            $dir = self::$config['path'] ?? sys_get_temp_dir() . '/app_cache';

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            return $dir;
        }

        private static function clearDirectory($directory)
        {
            if (!is_dir($directory)) {
                return false;
            }

            $files = array_diff(scandir($directory), ['.', '..']);

            foreach ($files as $file) {
                $path = $directory . DIRECTORY_SEPARATOR . $file;
                is_dir($path) ? self::clearDirectory($path) : unlink($path);
            }

            return true;
        }

        private static function prefixKey($key)
        {
            return self::$prefix . $key;
        }

        // Redis implementation (stubs - need Redis extension)
        private static function redisGet($key, $default)
        {
            // Implementation for Redis
            return $default;
        }

        private static function redisSet($key, $value, $ttl)
        {
            // Implementation for Redis
            return true;
        }

        private static function redisDelete($key)
        {
            // Implementation for Redis
            return true;
        }

        private static function redisClear()
        {
            // Implementation for Redis
            return true;
        }
    }
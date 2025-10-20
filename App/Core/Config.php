<?php

    namespace App\Core;

    class Config
    {
        private static $config = [];
        private static $loaded = false;

        public static function load()
        {
            if (self::$loaded) {
                return;
            }

            // Try to load from cache first
            $cachedConfig = Cache::get('app_config');

            if ($cachedConfig) {
                self::$config = $cachedConfig;
                self::$loaded = true;
                return;
            }

            $configPath = BASE_PATH . '/config';

            if (!is_dir($configPath)) {
                throw new \RuntimeException('Config directory not found');
            }

            $configFiles = glob($configPath . '/*.php');

            foreach ($configFiles as $configFile) {
                $key = basename($configFile, '.php');
                self::$config[$key] = require $configFile;
            }

            // Cache the configuration
            Cache::set('app_config', self::$config, 3600); // 1 hour

            self::$loaded = true;
        }

    }
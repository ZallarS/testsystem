<?php

    spl_autoload_register(function ($class) {
        $prefixes = [
            'App\\' => __DIR__ . '/App/',
            'Plugins\\' => PLUGINS_PATH,
            'Routes\\' => __DIR__ . '/routes/',
        ];

        foreach ($prefixes as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    });

    // Определяем константы
    define('BASE_PATH', realpath(__DIR__));
    define('APP_PATH', BASE_PATH . '/App/');
    define('CORE_PATH', APP_PATH . 'Core/');
    define('PLUGINS_PATH', BASE_PATH . '/Plugins/');
    define('STORAGE_PATH', BASE_PATH . '/storage/');
    define('VIEWS_PATH', APP_PATH . 'Views/');
    define('DATABASE_PATH', BASE_PATH . '/database/');
    define('MIGRATIONS_PATH', DATABASE_PATH . 'migrations/');
    define('SEEDS_PATH', DATABASE_PATH . 'seeds/');
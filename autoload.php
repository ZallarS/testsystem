<?php

    require_once __DIR__ . '/App/Core/Helpers.php';

    spl_autoload_register(function ($class) {
        $prefixes = [
            'App\\' => __DIR__ . '/App/',
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
    define('STORAGE_PATH', BASE_PATH . '/storage/');
    define('VIEWS_PATH', APP_PATH . 'Views/');
    define('DATABASE_PATH', BASE_PATH . '/database/');
    define('MIGRATIONS_PATH', DATABASE_PATH . 'migrations/');
    define('SEEDS_PATH', DATABASE_PATH . 'seeds/');

    if (!function_exists('e')) {
        function e($value) {
            if (is_array($value)) {
                return array_map('e', $value);
            }
            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            return $value;
        }
    }

    // Создаем необходимые директории, если они не существуют
    $directories = [STORAGE_PATH];
    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

// Загружаем переменные окружения до инициализации приложения
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    \App\Core\Env::load($envFile);
} else {
    // В development режиме создаем .env автоматически
    if (php_sapi_name() === 'cli' || ($_SERVER['HTTP_HOST'] ?? '') === 'localhost') {
        $defaultEnv = "APP_ENV=development\nAPP_SECRET=dev-" . bin2hex(random_bytes(16)) . "\nAPP_DEBUG=true\nDB_HOST=localhost\nDB_DATABASE=testsystem\nDB_USERNAME=root\nDB_PASSWORD=";
        file_put_contents($envFile, $defaultEnv);
        \App\Core\Env::load($envFile);
        error_log("Auto-created .env file for development");
    }
}

// Создаем необходимые директории
$directories = [
    STORAGE_PATH,
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/sessions',
    STORAGE_PATH . '/backups'
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}
\App\Core\AuditLogger::initialize();


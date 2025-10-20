<?php

    namespace App\Core;

    class Application
    {
        private $container;
        private $router;

        private static $instance;

        public function __construct()
        {
            self::$instance = $this;
            $this->validateEnvironment(); // Добавляем валидацию окружения
            $this->initializeErrorHandling();
            $this->initializeContainer();
            $this->initializeRouter();
        }

        private function initializeErrorHandling()
        {
            if ($this->isDevelopment()) {
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
            } else {
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            }

            set_exception_handler([$this, 'handleException']);
        }
        public static function getContainer()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance->container;
        }

        private function isDevelopment()
        {
            return ($_ENV['APP_ENV'] ?? 'production') === 'development';
        }

        public function handleException($exception)
        {
            $errorId = uniqid('err_', true);
            $errorMessage = $exception->getMessage();

            error_log("Error ID: $errorId - " . $exception->getMessage());
            error_log("Stack trace: " . $exception->getTraceAsString());

            http_response_code(500);

            if ($this->isDevelopment()) {
                $message = e($errorMessage);
                $file = e($exception->getFile());
                $line = $exception->getLine();
                $trace = e($exception->getTraceAsString());

                echo "<h1>Error: " . $message . "</h1>";
                echo "<p>File: " . $file . ":" . $line . "</p>";
                echo "<p>Error ID: " . e($errorId) . "</p>";
                echo "<pre>" . $trace . "</pre>";
            } else {
                // В production показываем только общую ошибку
                echo "An error occurred. Error ID: " . e($errorId);
                // Логируем детали для администратора
                error_log("Production error [{$errorId}]: " . $exception->getMessage());
            }

            exit(1);
        }

        private function initializeContainer()
        {
            $this->container = new Container();

            // ДОБАВЛЯЕМ проверку на случай отсутствия конфигурационных файлов
            try {
                $appConfig = $this->container->config('app');
                if (!$appConfig) {
                    // Если конфигурация не загружена, устанавливаем значения по умолчанию
                    $this->container->set('app_config', [
                        'secret' => $_ENV['APP_SECRET'] ?? 'fallback-secret-key',
                        'env' => $_ENV['APP_ENV'] ?? 'production'
                    ]);
                }
            } catch (\Exception $e) {
                // Игнорируем ошибки конфигурации на этом этапе
                error_log("Configuration loading warning: " . $e->getMessage());
            }
        }

        private function initializeRouter()
        {
            $this->router = new Router();
            $this->container->set('router', $this->router);
        }

        public function boot()
        {
            // Добавляем SessionMiddleware глобально
            $this->router->middleware([new \App\Middleware\SessionMiddleware()]);
            $this->router->middleware([new \App\Middleware\VerifyCsrfToken()]);

            // Регистрируем основные маршруты приложения
            $this->registerRoutes();
        }

        public function registerRoutes()
        {
            // Сохраняем ссылки на роутер и контейнер для использования в замыканиях
            $router = $this->router;
            $container = $this->container;

            // Загружаем веб-маршруты
            $webRoutesFile = BASE_PATH . '/routes/web.php';
            if (file_exists($webRoutesFile)) {
                require $webRoutesFile;
            }

            // Загружаем API-маршруты (если есть)
            $apiRoutesFile = BASE_PATH . '/routes/api.php';
            if (file_exists($apiRoutesFile)) {
                require $apiRoutesFile;
            }

        }

        public function run()
        {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Убираем завершающий слэш
            if ($path !== '/' && substr($path, -1) === '/') {
                $path = substr($path, 0, -1);
            }

            // Диагностика - БЕЗОПАСНОЕ логирование
            error_log("Request: $method $path");
            error_log("Session status: " . session_status());
            error_log("Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'none'));
            error_log("Session name: " . session_name());
            error_log("Cookies: " . print_r($_COOKIE, true));

            // Обработка запроса
            try {
                $result = $this->router->dispatch($method, $path);

                if ($result instanceof Response) {
                    $result->send();
                } else {
                    echo $result;
                }
            } catch (\Exception $e) {
                error_log("Router dispatch error: " . $e->getMessage());
                $response = Response::make("500 - Internal Server Error", 500);
                $response->send();
            }
        }

        private function validateEnvironment()
        {
            $requiredEnvVars = [
                'APP_SECRET',
                'DB_HOST',
                'DB_DATABASE',
                'DB_USERNAME'
            ];

            $missing = [];
            foreach ($requiredEnvVars as $var) {
                if (empty($_ENV[$var])) {
                    $missing[] = $var;
                }
            }

            if (!empty($missing)) {
                throw new \RuntimeException(
                    'Missing required environment variables: ' . implode(', ', $missing)
                );
            }

            // Validate APP_SECRET is not default
            if ($_ENV['APP_SECRET'] === 'your-secret-key-change-this-in-production') {
                throw new \RuntimeException('Please change APP_SECRET from default value in production');
            }

            // Security checks for production
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                    error_log('SECURITY WARNING: Debug mode enabled in production');
                    // В production автоматически отключаем debug
                    $_ENV['APP_DEBUG'] = 'false';
                }

                if (!self::isSecure()) {
                    error_log('SECURITY WARNING: Not using HTTPS in production');
                }
            }
        }

        private static function isSecure()
        {
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        }


    }
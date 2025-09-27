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
            error_log("Uncaught exception: " . $exception->getMessage());

            if ($this->isDevelopment()) {
                $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
                $file = htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8');
                $line = $exception->getLine();
                $trace = htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');

                echo "<h1>Error: " . $message . "</h1>";
                echo "<p>File: " . $file . ":" . $line . "</p>";
                echo "<pre>" . $trace . "</pre>";
            } else {
                http_response_code(500);
                echo "An error occurred. Please try again later.";
            }

            exit(1);
        }

        private function initializeContainer()
        {
            $this->container = new Container();
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

            // Диагностика
            error_log("Request: $method $path");
            error_log("Session status: " . session_status());
            error_log("Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'none'));
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
                error_log("Error: " . $e->getMessage());
                $response = Response::make("500 - Internal Server Error", 500);
                $response->send();
            }
        }


    }
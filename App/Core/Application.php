<?php

    namespace App\Core;

    class Application
    {
        private $container;
        private $router;
        private $pluginManager;

        private static $instance;

        public function __construct()
        {
            self::$instance = $this;
            $this->initializeErrorHandling();
            $this->initializeContainer();
            $this->initializeRouter();
            $this->initializePluginManager();
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
                throw new \Exception('Application instance not initialized');
            }
            return self::$instance->container;
        }

        private function isDevelopment()
        {
            return ($_ENV['APP_ENV'] ?? 'production') === 'development';
        }

        public function handleException($exception)
        {
            error_log("Неперехваченное исключение: " . $exception->getMessage());

            // Всегда показываем ошибки в режиме разработки
            if ($this->isDevelopment() || ($_ENV['APP_DEBUG'] ?? false)) {
                echo "<h1>Error: " . htmlspecialchars($exception->getMessage()) . "</h1>";
                echo "<p>File: " . htmlspecialchars($exception->getFile()) . ":" . $exception->getLine() . "</p>";
                echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
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

        private function initializePluginManager()
        {
            $this->pluginManager = new PluginManager();
            $this->container->set('plugin_manager', $this->pluginManager);

            // Добавьте проверку
            if (!$this->container->has('plugin_manager')) {
                throw new \Exception('Failed to register PluginManager in container');
            }
        }

        public function boot()
        {
            $this->pluginManager->loadActivePlugins();
            $this->pluginManager->registerPluginRoutes($this->router); // Должно быть ДО registerRoutes()
            $this->pluginManager->registerPluginServices($this->container);
            $this->pluginManager->bootActivePlugins();
            $this->registerRoutes(); // Основные маршруты загружаются после плагинов
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

            // Загружаем маршруты из плагинов
            $this->loadPluginRoutes();
        }

        private function loadPluginRoutes()
        {
            $pluginManager = $this->container->get('plugin_manager');
            $activePlugins = $pluginManager->getActivePlugins();

            foreach ($activePlugins as $pluginName => $plugin) {
                $pluginRoutesFile = PLUGINS_PATH . $pluginName . '/routes.php';
                if (file_exists($pluginRoutesFile)) {
                    // Делаем $router доступной в файле маршрутов плагина
                    $router = $this->router;
                    require $pluginRoutesFile;
                }
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

            // Отладочная информация
            if ($path === '/debug-routes') {
                $this->showDebugInfo();
                return;
            }

            // Обработка запроса
            try {
                $result = $this->router->dispatch($method, $path);

                if ($result instanceof Response) {
                    $result->send();
                } else {
                    echo $result;
                }
            } catch (\Exception $e) {
                $response = Response::make("500 - Internal Server Error", 500);
                $response->send();
                error_log("Ошибка: " . $e->getMessage());
            }
        }

        private function showDebugInfo()
        {
            echo "<h1>Registered Routes:</h1>";
            echo "<pre>";
            print_r($this->router->getRoutes());
            echo "</pre>";

            echo "<h1>Active Plugins:</h1>";
            echo "<pre>";
            print_r($this->pluginManager->getActivePlugins());
            echo "</pre>";
        }

    }
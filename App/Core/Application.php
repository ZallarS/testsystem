<?php

    namespace App\Core;

    class Application
    {
        private $container;
        private $router;
        private $bootstrappers = [];

        private static $instance;

        public function __construct()
        {
            self::$instance = $this;

            try {
                $this->registerBootstrappers();
                $this->bootstrap();
            } catch (\RuntimeException $e) {
                // Специальная обработка для ошибок окружения
                if (strpos($e->getMessage(), 'environment variables') !== false) {
                    $this->handleEnvironmentError($e);
                } else {
                    $this->handleException($e);
                }
            } catch (\Exception $e) {
                $this->handleException($e);
            } catch (\Throwable $e) {
                $this->handleException($e);
            }
        }

        private function registerBootstrappers()
        {
            $this->bootstrappers = [
                new EnvironmentBootstrapper(),
                new ErrorHandlingBootstrapper(),
                new ContainerBootstrapper(),
                new RouterBootstrapper(),
                new MiddlewareBootstrapper(),
            ];
        }

        private function bootstrap()
        {
            foreach ($this->bootstrappers as $bootstrapper) {
                $bootstrapper->bootstrap($this);
            }
        }

        public function getContainer()
        {
            return $this->container;
        }

        public function setContainer(Container $container)
        {
            $this->container = $container;
        }

        public function getRouter()
        {
            return $this->router;
        }

        public function setRouter(Router $router)
        {
            $this->router = $router;
        }

        public function run()
        {
            try {
                $request = Request::createFromGlobals();
                $response = $this->handle($request);
                $response->send();
            } catch (\Exception $e) {
                $this->handleException($e);
            } catch (\Throwable $e) {
                $this->handleException($e);
            }
        }

        public function handle(Request $request)
        {
            try {
                $method = $request->getMethod();
                $path = $request->getPathInfo();

                return $this->router->dispatch($method, $path);
            } catch (\Exception $e) {
                return $this->handleException($e);
            } catch (\Throwable $e) {
                return $this->handleException($e);
            }
        }

        private function handleException($e)
        {
            // Используем ErrorHandler напрямую
            \App\Core\ErrorHandler::handleException($e);
        }

        private function handleEnvironmentError(\RuntimeException $e)
        {
            http_response_code(500);

            if ($this->isDevelopment() || php_sapi_name() === 'cli') {
                // Детальная информация для разработки
                echo "<h1>Environment Configuration Error</h1>";
                echo "<p>{$e->getMessage()}</p>";

                if ($this->isDevelopment()) {
                    echo "<h2>Quick Fix:</h2>";
                    echo "<p>Create a <code>.env</code> file in your project root with:</p>";
                    echo "<pre>";
                    echo "APP_ENV=development\n";
                    echo "APP_SECRET=your-secret-key-change-this-in-production\n";
                    echo "DB_HOST=127.0.0.1\n";
                    echo "DB_DATABASE=new_testsystem\n";
                    echo "DB_USERNAME=root\n";
                    echo "DB_PASSWORD=wFb-3OD6pU\n";
                    echo "</pre>";
                }
            } else {
                // Общая ошибка для production
                echo "<h1>Configuration Error</h1>";
                echo "<p>Please contact system administrator.</p>";
            }

            exit(1);
        }

        private function isDevelopment()
        {
            return ($_ENV['APP_ENV'] ?? 'production') === 'development';
        }
    }
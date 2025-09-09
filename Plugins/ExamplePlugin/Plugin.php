<?php

    namespace Plugins\ExamplePlugin;

    use App\Core\AbstractPlugin;
    use App\Core\Hooks;

    class Plugin extends AbstractPlugin {
        protected $name = 'ExamplePlugin'; // Измените с 'Core' на 'ExamplePlugin'
        protected $version = '0.0.1';
        protected $description = 'Пример плагина для тестирования системы';
        protected $author = 'Администратор';

        public function boot() {
            // Базовая реализация метода boot
            error_log("ExamplePlugin booted");
        }

        public function activate() {
            // Активация плагина
            error_log("ExamplePlugin activated");
        }

        public function deactivate() {
            // Деактивация плагина
            error_log("ExamplePlugin deactivated");
        }

        public function registerRoutes($router) {
            $routesFile = PLUGINS_PATH . $this->name . '/routes.php';
            error_log("Registering routes for plugin: " . $this->name . ", file: " . $routesFile);
            if (file_exists($routesFile)) {
                error_log("Routes file exists, including...");
                // Делаем $router доступной в файле маршрутов
                (function() use ($router, $routesFile) {
                    include $routesFile;
                })();
                error_log("Routes included successfully");
            } else {
                error_log("Routes file does not exist: " . $routesFile);
            }
        }

        public function registerServices($container) {
            // Регистрируем сервисы плагина через метод set(), а не как массив
            $container->set('example_service', function() {
                // Создаем простой сервис для примера
                return new class {
                    public function test() {
                        return "Тест сервиса плагина";
                    }
                };
            });
        }

        public function registerEvents($dispatcher) {
            // Регистрация событий плагина
        }

        public function registerHooks() {
            // Регистрация хуков плагина
            Hooks::addAction('admin_menu', [$this, 'addAdminMenu']);
        }

        public function addAdminMenu() {
            // Добавляем пункт меню в админку
            echo '<li><a href="/admin/example">Example Plugin</a></li>';
        }
    }
<?php

    namespace App\Core;

    abstract class AbstractPlugin implements PluginInterface {
        protected $name;
        protected $version;
        protected $description;
        protected $author;

        public function __construct() {
            // Базовая инициализация
        }

        public function boot() {
            // Базовая реализация метода boot
        }

        public function activate() {
            // Активация плагина
        }

        public function deactivate() {
            // Деактивация плагина
        }

        public function registerRoutes($router)
        {
            $routesFile = PLUGINS_PATH . $this->name . '/routes.php';
            if (file_exists($routesFile)) {
                // Временно добавьте логирование для отладки
                error_log("Loading routes from: " . $routesFile);
                (function() use ($router, $routesFile) {
                    include $routesFile;
                })();
                error_log("Routes loaded successfully");
            } else {
                error_log("Routes file not found: " . $routesFile);
            }
        }

        public function registerServices($container) {
            // Регистрация сервисов плагина
        }

        public function registerEvents($dispatcher) {
            // Реализация по умолчанию - пустая
        }

        public function registerHooks() {
            // Реализация по умолчанию - пустая
        }
        public function getName() {
            return $this->name;
        }

        public function getVersion() {
            return $this->version;
        }

        public function getDescription() {
            return $this->description;
        }

        public function getAuthor() {
            return $this->author;
        }
    }

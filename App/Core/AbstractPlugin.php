<?php

    namespace App\Core;

    abstract class AbstractPlugin implements PluginInterface {
        protected $name;
        protected $version;
        protected $description;
        protected $author;
        protected $path;

        public function __construct() {
            // Автоматически определяем путь к плагину
            $reflection = new \ReflectionClass(get_class($this));
            $this->path = dirname($reflection->getFileName());
        }

        public function boot() {
            // Базовая реализация по умолчанию
        }

        public function activate() {
            // Базовая реализация по умолчанию
        }

        public function deactivate() {
            // Базовая реализация по умолчанию
        }

        public function registerRoutes($router) {
            $routesFile = $this->path . '/routes.php';
            if (file_exists($routesFile)) {
                (function() use ($router, $routesFile) {
                    include $routesFile;
                })();
            }
        }

        public function registerServices($container) {
            $servicesFile = $this->path . '/services.php';
            if (file_exists($servicesFile)) {
                (function() use ($container, $servicesFile) {
                    include $servicesFile;
                })();
            }
        }

        public function registerEvents($dispatcher) {
            $eventsFile = $this->path . '/events.php';
            if (file_exists($eventsFile)) {
                (function() use ($dispatcher, $eventsFile) {
                    include $eventsFile;
                })();
            }
        }

        public function registerHooks() {
            $hooksFile = $this->path . '/hooks.php';
            if (file_exists($hooksFile)) {
                (function() use ($hooksFile) {
                    include $hooksFile;
                })();
            }
        }

        // Геттеры
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

        public function getPath() {
            return $this->path;
        }
    }
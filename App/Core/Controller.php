<?php

    namespace App\Core;

    class Controller {
        protected $container;
        protected $pluginViewsPath = null;

        public function __construct(Container $container = null) {
            $this->container = $container;
        }

        protected function view($viewPath, $data = []) {
            // Извлекаем данные в переменные
            extract($data);

            // Определяем путь к файлу представления
            if ($this->pluginViewsPath) {
                // Используем путь плагина, если он установлен
                $viewFile = $this->pluginViewsPath . $viewPath . '.php';
            } else {
                // Используем стандартный путь приложения
                $viewFile = VIEWS_PATH . $viewPath . '.php';
            }

            // Проверяем существование файла
            if (!file_exists($viewFile)) {
                throw new \Exception("View file not found: " . $viewFile);
            }

            // Используем основной layout
            $layoutFile = VIEWS_PATH . 'layout/main.php';
            $content = $viewFile;

            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                // Если layout не существует, загружаем только view
                require $viewFile;
            }
        }

        protected function json($data, $statusCode = 200) {
            return Response::json($data, $statusCode);
        }

        protected function redirect($url, $statusCode = 302) {
            return Response::redirect($url, $statusCode);
        }

        protected function get($service) {
            return $this->container ? $this->container->get($service) : null;
        }
    }
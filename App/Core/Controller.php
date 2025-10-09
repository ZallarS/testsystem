<?php

    namespace App\Core;

    class Controller {
        protected $container;

        public function __construct(Container $container = null) {
            $this->container = $container;
        }

        protected function view($viewPath, $data = [])
        {
            // Извлекаем данные в переменные
            extract($data);

            // Используем стандартный путь приложения
            $viewFile = VIEWS_PATH . $viewPath . '.php';

            // Проверяем существование файла
            if (!file_exists($viewFile)) {
                throw new \Exception("View file not found: " . $viewFile);
            }

            // Буферизуем вывод view
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            // Используем основной layout
            $layoutFile = VIEWS_PATH . 'layout/main.php';

            if (file_exists($layoutFile)) {
                // Рендерим layout с содержимым view
                ob_start();
                require $layoutFile;
                $finalContent = ob_get_clean();

                return new \App\Core\Response($finalContent);
            } else {
                // Если layout не существует, загружаем только view
                return new \App\Core\Response($content);
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
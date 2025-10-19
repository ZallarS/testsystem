<?php

    namespace App\Core;

    class ViewRenderer
    {
        private $data = [];
        private $autoEscape = true;

        public function render($viewPath, $data = [])
        {
            $this->data = $data;
            $viewFile = VIEWS_PATH . $viewPath . '.php';

            if (!file_exists($viewFile)) {
                throw new \Exception("View file not found: " . $viewFile);
            }

            // Извлекаем данные с экранированием
            extract($this->escapeData($this->data));

            ob_start();
            include $viewFile;
            $content = ob_get_clean();

            return $this->renderWithLayout($content);
        }

        private function escapeData($data)
        {
            if (!$this->autoEscape) {
                return $data;
            }

            $escaped = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $escaped[$key] = $this->escapeData($value);
                } elseif (is_string($value)) {
                    $escaped[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } else {
                    $escaped[$key] = $value;
                }
            }
            return $escaped;
        }

        public function e($value)
        {
            if (is_array($value)) {
                return array_map([$this, 'e'], $value);
            }

            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }

            return $value;
        }

        public function raw($value)
        {
            $this->autoEscape = false;
            $result = $value;
            $this->autoEscape = true;
            return $result;
        }

        public function safeHtml($html)
        {
            $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><code><pre>';
            return strip_tags($html, $allowedTags);
        }

        private function renderWithLayout($content)
        {
            $layoutFile = VIEWS_PATH . 'layout/main.php';

            if (!file_exists($layoutFile)) {
                return $content;
            }

            // Передаем контент в layout
            $this->data['content'] = $content;
            extract($this->escapeData($this->data));

            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }
    }
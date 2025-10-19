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

        public function e($value, $context = 'html')
        {
            switch ($context) {
                case 'html':
                    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                case 'attr':
                    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                case 'js':
                    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                case 'css':
                    // Экранирование для CSS
                    return preg_replace('/[^a-zA-Z0-9]/', '', $value);
                default:
                    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }
        }

        public function raw($value)
        {
            $this->autoEscape = false;
            $result = $value;
            $this->autoEscape = true;
            return $result;
        }

        public function unsafeRaw($value)
        {
            trigger_error('Using unsafeRaw method - potential XSS vulnerability', E_USER_WARNING);
            return $value;
        }

        // УЛУЧШИТЬ safeHtml:
        public function safeHtml($html, $allowedTags = null)
        {
            if ($allowedTags === null) {
                $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><code><pre><span><div>';
            }

            // Удаляем небезопасные атрибуты
            $html = preg_replace('/\s+on\w+=\s*[\'"]?[^\'"]*[\'"]?/i', '', $html);
            $html = preg_replace('/\s+style=\s*[\'"]?[^\'"]*[\'"]?/i', '', $html);

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
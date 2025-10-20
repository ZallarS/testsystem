<?php

    namespace App\Core;

    class ViewRenderer
    {
        private $autoEscape = true;

        public function render($viewPath, $data = [])
        {
            $viewFile = VIEWS_PATH . $viewPath . '.php';

            if (!file_exists($viewFile)) {
                throw new \Exception("View file not found: " . $viewFile);
            }

            // Фильтруем ключи данных для предотвращения injection
            $safeData = $this->filterViewData($data);

            // Извлекаем данные безопасно
            extract($safeData, EXTR_SKIP);

            // Начинаем буферизацию
            ob_start();

            // Включаем view файл
            include $viewFile;

            // Получаем содержимое буфера
            return ob_get_clean();
        }

        private function filterViewData($data)
        {
            $safeData = [];
            $reservedVars = ['GLOBALS', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV', 'this'];

            foreach ($data as $key => $value) {
                // Проверяем, что ключ не является зарезервированным именем
                if (in_array(strtoupper($key), $reservedVars)) {
                    throw new \InvalidArgumentException("Reserved variable name used in view data: {$key}");
                }

                // Разрешаем только алфавитно-цифровые символы и подчеркивания
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                    throw new \InvalidArgumentException("Invalid variable name in view data: {$key}");
                }

                $safeData[$key] = $value;
            }

            return $safeData;
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
                return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            return $value;
        }

        // УДАЛЯЕМ метод raw() как небезопасный
        // Вместо него используем safeHtml для ограниченного набора HTML

        public function safeHtml($html, $allowedTags = null)
        {
            if ($allowedTags === null) {
                $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><code><pre><span><div><h1><h2><h3><h4><h5><h6>';
            }

            // Удаляем опасные атрибуты
            $html = preg_replace('/\s+on\w+\s*=\s*[\'"]?[^\'"]*[\'"]?/i', '', $html);
            $html = preg_replace('/\s+style\s*=\s*[\'"]?[^\'"]*[\'"]?/i', '', $html);
            $html = preg_replace('/\s+href\s*=\s*[\'"]?javascript:[^\'"]*[\'"]?/i', '', $html);

            return strip_tags($html, $allowedTags);
        }
    }
<?php

    namespace App\Core;

    class Helpers
    {
        public static function e($string)
        {
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }

        public static function copyDirectory($source, $destination)
        {
            if (!is_dir($source)) {
                throw new \InvalidArgumentException('Source is not a directory');
            }

            // Нормализуем пути
            $source = realpath($source);
            if ($source === false) {
                throw new \InvalidArgumentException('Source directory does not exist');
            }

            $destination = rtrim($destination, DIRECTORY_SEPARATOR);

            // Проверяем, что пути безопасны
            if (!self::isSafePath($source) || !self::isSafePath($destination)) {
                throw new \InvalidArgumentException('Invalid path provided');
            }

            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $target = $destination . DIRECTORY_SEPARATOR . $files->getSubPathName();

                // Проверяем на path traversal
                if (!self::isSafePath($target)) {
                    throw new \InvalidArgumentException('Path traversal attempt detected');
                }

                if ($file->isDir()) {
                    if (!is_dir($target)) {
                        mkdir($target, 0755);
                    }
                } else {
                    copy($file->getPathname(), $target);
                }
            }
        }

        public static function csrfField()
        {
            return '<input type="hidden" name="csrf_token" value="' . self::e(CSRF::generateToken()) . '">';
        }

        public static function methodField($method)
        {
            $allowedMethods = ['PUT', 'PATCH', 'DELETE'];
            $method = strtoupper($method);

            if (in_array($method, $allowedMethods)) {
                return '<input type="hidden" name="_method" value="' . self::e($method) . '">';
            }

            return '';
        }

        private static function isSafePath($path)
        {
            // Запрещаем переходы на уровень выше базовой директории
            $basePath = realpath(BASE_PATH);
            $checkPath = realpath($path);

            if ($checkPath === false) {
                return false;
            }

            // Проверяем, что путь находится внутри базовой директории
            return strpos($checkPath, $basePath) === 0;
        }

        public static function safeHtml($html)
        {
            $renderer = new ViewRenderer();
            return $renderer->safeHtml($html);
        }

        public static function removeDirectory($directory)
        {
            if (!is_dir($directory)) {
                return false;
            }

            // Нормализуем путь и проверяем безопасность
            $directory = realpath($directory);
            if ($directory === false || !self::isSafePath($directory)) {
                throw new \InvalidArgumentException('Invalid directory path');
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }

            return rmdir($directory);
        }

        public static function camelCase($string)
        {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        }

        public static function snakeCase($string)
        {
            return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
        }
    }
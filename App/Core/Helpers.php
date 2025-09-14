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
            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $target = $destination . DIRECTORY_SEPARATOR . $files->getSubPathName();

                if ($file->isDir()) {
                    if (!is_dir($target)) {
                        mkdir($target, 0755);
                    }
                } else {
                    copy($file->getPathname(), $target);
                }
            }
        }

        public static function removeDirectory($directory)
        {
            if (!is_dir($directory)) {
                return false;
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
    }
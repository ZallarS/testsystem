<?php

    namespace App\Core;

    class Hooks {
        private static $actions = [];
        private static $filters = [];

        public static function addAction($hook, $callback, $priority = 10) {
            self::$actions[$hook][$priority][] = $callback;
        }

        public static function doAction($hook, ...$args) {
            if (isset(self::$actions[$hook])) {
                ksort(self::$actions[$hook]);
                foreach (self::$actions[$hook] as $priority => $callbacks) {
                    foreach ($callbacks as $callback) {
                        call_user_func_array($callback, $args);
                    }
                }
            }
        }

        public static function addFilter($hook, $callback, $priority = 10) {
            self::$filters[$hook][$priority][] = $callback;
        }

        public static function applyFilters($hook, $value, ...$args) {
            if (isset(self::$filters[$hook])) {
                ksort(self::$filters[$hook]);
                foreach (self::$filters[$hook] as $priority => $callbacks) {
                    foreach ($callbacks as $callback) {
                        $value = call_user_func_array($callback, array_merge([$value], $args));
                    }
                }
            }
            return $value;
        }
    }
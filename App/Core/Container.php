<?php

    namespace App\Core;

    class Container {
        private $services = [];
        private $config = [];

        public function __construct() {
            $this->loadConfig();
        }

        private function loadConfig() {
            $configPath = BASE_PATH . '/config/';
            $configFiles = glob($configPath . '*.php');

            foreach ($configFiles as $configFile) {
                $key = basename($configFile, '.php');
                $this->config[$key] = require $configFile;
            }
        }

        public function set($name, $value) {
            $this->services[$name] = $value;
        }

        public function get($name) {
            if (isset($this->services[$name])) {
                return $this->services[$name];
            }

            // Попытка создать сервис, если он не зарегистрирован
            if (class_exists($name)) {
                $service = new $name($this);
                $this->services[$name] = $service;
                return $service;
            }

            return null;
        }

        public function config($key, $default = null) {
            $keys = explode('.', $key);
            $value = $this->config;

            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }

            return $value;
        }

        public function has($name) {
            return isset($this->services[$name]) || class_exists($name);
        }
    }
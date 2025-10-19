<?php

    namespace App\Core;

    class Container {
        private $aliases = [];
        private $singletons = [];
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

        public function alias($abstract, $concrete)
        {
            $this->aliases[$abstract] = $concrete;
        }

        public function singleton($name, $concrete = null)
        {
            if ($concrete === null) {
                $concrete = $name;
            }
            $this->singletons[$name] = $concrete;
        }

        public function get($name)
        {
            // Проверяем алиасы
            if (isset($this->aliases[$name])) {
                $name = $this->aliases[$name];
            }

            if (isset($this->services[$name])) {
                return $this->services[$name];
            }

            // Проверяем синглтоны
            if (isset($this->singletons[$name]) && isset($this->services[$this->singletons[$name]])) {
                return $this->services[$this->singletons[$name]];
            }

            // Автоматическое создание с внедрением зависимостей
            if (class_exists($name)) {
                $service = $this->build($name);

                // Регистрируем синглтон если нужно
                if (isset($this->singletons[$name])) {
                    $this->services[$name] = $service;
                }

                return $service;
            }

            return null;
        }

        private function build($class)
        {
            $reflector = new \ReflectionClass($class);

            if (!$reflector->isInstantiable()) {
                throw new \Exception("Class {$class} is not instantiable");
            }

            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return new $class;
            }

            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $dependency = $parameter->getType();

                if (is_null($dependency)) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new \Exception("Cannot resolve dependency {$parameter->getName()}");
                    }
                } else {
                    $dependencies[] = $this->get($dependency->getName());
                }
            }

            return $reflector->newInstanceArgs($dependencies);
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
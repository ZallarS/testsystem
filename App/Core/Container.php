<?php

    namespace App\Core;

    class Container
    {
        private $bindings = [];
        private $instances = [];
        private $aliases = [];
        private $resolvingCallbacks = [];

        public function bind($abstract, $concrete = null, $shared = false)
        {
            if (is_null($concrete)) {
                $concrete = $abstract;
            }

            $this->bindings[$abstract] = [
                'concrete' => $concrete,
                'shared' => $shared
            ];
        }

        public function singleton($abstract, $concrete = null)
        {
            $this->bind($abstract, $concrete, true);
        }

        public function instance($abstract, $instance)
        {
            $this->instances[$abstract] = $instance;
        }

        public function alias($abstract, $alias)
        {
            $this->aliases[$alias] = $abstract;
        }

        public function get($abstract)
        {
            // Resolve aliases
            $abstract = $this->getAlias($abstract);

            // Return existing instance
            if (isset($this->instances[$abstract])) {
                return $this->instances[$abstract];
            }

            // Get binding
            $binding = $this->getBinding($abstract);

            // Build instance
            $object = $this->build($binding['concrete']);

            // Store if shared
            if ($binding['shared']) {
                $this->instances[$abstract] = $object;
            }

            // Call resolving callbacks
            $this->fireResolvingCallbacks($abstract, $object);

            return $object;
        }

        private function build($concrete)
        {
            if (is_callable($concrete)) {
                return $concrete($this);
            }

            if (!class_exists($concrete)) {
                throw new \Exception("Class {$concrete} does not exist");
            }

            $reflector = new \ReflectionClass($concrete);

            if (!$reflector->isInstantiable()) {
                throw new \Exception("Class {$concrete} is not instantiable");
            }

            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return new $concrete;
            }

            $dependencies = $this->resolveDependencies($constructor->getParameters());

            return $reflector->newInstanceArgs($dependencies);
        }

        private function resolveDependencies(array $parameters)
        {
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

            return $dependencies;
        }

        private function getAlias($abstract)
        {
            return $this->aliases[$abstract] ?? $abstract;
        }

        private function getBinding($abstract)
        {
            if (!isset($this->bindings[$abstract])) {
                // Auto-bind if not registered
                return ['concrete' => $abstract, 'shared' => false];
            }

            return $this->bindings[$abstract];
        }

        private function fireResolvingCallbacks($abstract, $object)
        {
            foreach ($this->resolvingCallbacks as $callback) {
                call_user_func($callback, $object, $this);
            }
        }

        public function resolving($callback)
        {
            $this->resolvingCallbacks[] = $callback;
        }

        public function has($abstract)
        {
            return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
        }
    }
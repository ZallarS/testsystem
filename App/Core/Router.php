<?php

    namespace App\Core;

    class Router {
        private $routes = [];
        private $middleware = [];

        public function addRoute($method, $path, $handler, $middleware = []) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $path);
            $pattern = "#^" . $pattern . "$#";

            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $path,
                'pattern' => $pattern,
                'handler' => $handler,
                'middleware' => $middleware
            ];
        }

        public function get($path, $handler, $middleware = []) {
            $this->addRoute('GET', $path, $handler, $middleware);
            return $this; // Возвращаем $this для цепочки вызовов
        }

        public function post($path, $handler, $middleware = []) {
            $this->addRoute('POST', $path, $handler, $middleware);
            return $this;
        }

        public function put($path, $handler, $middleware = []) {
            $this->addRoute('PUT', $path, $handler, $middleware);
            return $this;
        }

        public function delete($path, $handler, $middleware = []) {
            $this->addRoute('DELETE', $path, $handler, $middleware);
            return $this;
        }

        public function patch($path, $handler, $middleware = []) {
            $this->addRoute('PATCH', $path, $handler, $middleware);
            return $this;
        }

        public function middleware($middleware) {
            if (!empty($this->routes)) {
                $lastIndex = count($this->routes) - 1;
                $this->routes[$lastIndex]['middleware'] = array_merge(
                    $this->routes[$lastIndex]['middleware'],
                    (array)$middleware
                );
            }
            return $this;
        }

        public function getRoutes() {
            return $this->routes;
        }

        public function dispatch($method, $path) {
            $method = strtoupper($method);

            foreach ($this->routes as $route) {
                if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    // Выполняем middleware
                    if (!empty($route['middleware'])) {
                        $middlewareStack = $route['middleware'];
                        $middlewareIndex = 0;

                        $next = function() use (&$middlewareIndex, $middlewareStack, $route, $params, &$next) {
                            if ($middlewareIndex < count($middlewareStack)) {
                                $middleware = $middlewareStack[$middlewareIndex++];

                                // Если middleware - это строка (имя класса), создаем экземпляр
                                if (is_string($middleware)) {
                                    $middleware = new $middleware();
                                }

                                // Если middleware - уже экземпляр, вызываем handle
                                if (is_object($middleware) && method_exists($middleware, 'handle')) {
                                    return $middleware->handle($next);
                                }
                            } else {
                                // Все middleware выполнены, выполняем обработчик маршрута
                                return $this->executeHandler($route['handler'], $params);
                            }
                        };

                        return $next();
                    }

                    return $this->executeHandler($route['handler'], $params);
                }
            }

            http_response_code(404);
            return '404 - Page not found';
        }

        private function executeHandler($handler, $params) {
            if (is_array($handler) && count($handler) === 2) {
                $controller = $handler[0];
                $action = $handler[1];

                if (class_exists($controller)) {
                    $controllerInstance = new $controller();
                    if (method_exists($controllerInstance, $action)) {
                        return call_user_func_array([$controllerInstance, $action], $params);
                    }
                }
            }

            return call_user_func_array($handler, $params);
        }

        public function group($prefix, $callback, $options = [])
        {
            $currentMiddleware = $this->middleware;

            // Добавляем middleware из опций группы
            if (!empty($options['middleware'])) {
                $this->middleware = array_merge($this->middleware, (array)$options['middleware']);
            }

            // Сохраняем текущие маршруты
            $currentRoutes = $this->routes;
            $this->routes = [];

            // Вызываем callback для определения маршрутов в группе
            $callback();

            // Добавляем префикс ко всем маршрутам в группе
            $groupRoutes = $this->routes;
            $this->routes = $currentRoutes;

            foreach ($groupRoutes as $route) {
                // Правильно объединяем префикс и путь маршрута
                $route['path'] = $this->joinPath($prefix, $route['path']);

                // Обновляем pattern для нового пути
                $route['pattern'] = $this->buildPattern($route['path']);

                // Добавляем middleware группы к middleware маршрута
                $route['middleware'] = array_merge($this->middleware, $route['middleware']);
                $this->routes[] = $route;
            }

            // Восстанавливаем предыдущие middleware
            $this->middleware = $currentMiddleware;

            return $this;
        }

        private function joinPath($prefix, $path)
        {
            // Убираем слэши с обеих сторон и соединяем одним слэшем
            $prefix = trim($prefix, '/');
            $path = trim($path, '/');

            if ($prefix === '') {
                return '/' . $path;
            }

            if ($path === '') {
                return '/' . $prefix;
            }

            return '/' . $prefix . '/' . $path;
        }

        private function buildPattern($path)
        {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $path);
            return "#^{$pattern}$#";
        }

        private function executeMiddlewareStack($middlewareStack, $route, $params)
        {
            $middlewareIndex = 0;

            $next = function() use (&$middlewareIndex, $middlewareStack, $route, $params, &$next) {
                if ($middlewareIndex < count($middlewareStack)) {
                    $middleware = $middlewareStack[$middlewareIndex++];

                    if (is_string($middleware)) {
                        $middleware = new $middleware();
                    }

                    if (is_object($middleware) && method_exists($middleware, 'handle')) {
                        return $middleware->handle($next);
                    } else {
                        throw new \Exception("Middleware must have a handle method");
                    }
                } else {
                    return $this->executeHandler($route['handler'], $params);
                }
            };

            return $next();
        }
    }
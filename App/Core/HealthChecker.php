<?php

    namespace App\Core;

    class HealthChecker
    {
        private $checks = [];

        public function __construct()
        {
            $this->registerDefaultChecks();
        }

        public function registerCheck($name, callable $check)
        {
            $this->checks[$name] = $check;
        }

        public function check()
        {
            $results = [
                'status' => 'healthy',
                'timestamp' => date('c'),
                'checks' => []
            ];

            foreach ($this->checks as $name => $check) {
                try {
                    $startTime = microtime(true);
                    $checkResult = call_user_func($check);
                    $endTime = microtime(true);

                    $results['checks'][$name] = [
                        'status' => 'healthy',
                        'duration' => round(($endTime - $startTime) * 1000, 2) . 'ms',
                        'data' => $checkResult
                    ];
                } catch (\Exception $e) {
                    $results['status'] = 'unhealthy';
                    $results['checks'][$name] = [
                        'status' => 'unhealthy',
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;
        }

        private function registerDefaultChecks()
        {
            $this->registerCheck('database', function() {
                $db = Database\Connection::getInstance()->getPdo();
                $stmt = $db->query('SELECT 1');
                return $stmt->fetch(\PDO::FETCH_COLUMN) === 1;
            });

            $this->registerCheck('cache', function() {
                $key = 'health_check_' . uniqid();
                $value = 'test_value';

                Cache::set($key, $value, 60);
                $retrieved = Cache::get($key);
                Cache::delete($key);

                return $retrieved === $value;
            });

            $this->registerCheck('storage', function() {
                $testFile = STORAGE_PATH . '/health_check.txt';
                $content = 'test';

                if (file_put_contents($testFile, $content) === false) {
                    throw new \Exception('Cannot write to storage');
                }

                if (file_get_contents($testFile) !== $content) {
                    throw new \Exception('Cannot read from storage');
                }

                unlink($testFile);
                return true;
            });

            $this->registerCheck('environment', function() {
                $requiredVars = ['APP_SECRET', 'DB_HOST', 'DB_DATABASE'];
                $missing = [];

                foreach ($requiredVars as $var) {
                    if (empty($_ENV[$var])) {
                        $missing[] = $var;
                    }
                }

                if (!empty($missing)) {
                    throw new \Exception('Missing environment variables: ' . implode(', ', $missing));
                }

                return [
                    'app_env' => $_ENV['APP_ENV'] ?? 'production',
                    'debug' => $_ENV['APP_DEBUG'] ?? false
                ];
            });
        }

        public function getStatus()
        {
            $results = $this->check();

            if ($results['status'] === 'healthy') {
                return Response::json($results, 200);
            } else {
                return Response::json($results, 503);
            }
        }
    }
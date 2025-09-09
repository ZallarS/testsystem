<?php

    namespace App\Core\Database;

    use PDO;
    use PDOException;

    class Connection
    {
        private static $instance = null;
        private $pdo;

        private function __construct()
        {
            // Загружаем конфигурацию базы данных
            $config = $this->loadConfig();

            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";

            try {
                $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }

        private function loadConfig()
        {
            // Проверяем, загружены ли переменные окружения
            if (empty($_ENV['DB_HOST'])) {
                $this->loadEnv();
            }

            return [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_DATABASE'] ?? 'test_system',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => 'utf8mb4',
            ];
        }

        private function loadEnv()
        {
            $envPath = BASE_PATH . '/.env';
            if (!file_exists($envPath)) {
                return;
            }

            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if (!array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                }
            }
        }

        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function getPdo()
        {
            return $this->pdo;
        }

        public function query($sql, $params = [])
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }

        public function lastInsertId()
        {
            return $this->pdo->lastInsertId();
        }

        public function beginTransaction()
        {
            return $this->pdo->beginTransaction();
        }

        public function commit()
        {
            return $this->pdo->commit();
        }

        public function rollBack()
        {
            return $this->pdo->rollBack();
        }
    }
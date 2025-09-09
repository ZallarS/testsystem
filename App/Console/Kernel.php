<?php

    namespace App\Console;

    use App\Core\Database\Migrator;
    use App\Core\Model;
    use App\Core\Database\Connection;

    class Kernel
    {
        protected $commands = [
            'migrate:run' => 'Run the database migrations',
            'migrate:create' => 'Create a new migration file',
            'migrate:reset' => 'Rollback all migrations',
            'migrate:rollback' => 'Rollback the last migration',
            'migrate:refresh' => 'Reset and re-run all migrations',
            'db:seed' => 'Run database seeder',
            'make:controller' => 'Create a new controller class',
            'make:model' => 'Create a new model class',
            'route:list' => 'Display all registered routes',
        ];

        public function handle($args)
        {
            $command = $args[1] ?? null;

            if (!$command || $command === 'help') {
                return $this->showHelp();
            }

            // Загрузка окружения
            $this->loadEnvironment();

            // Инициализация базы данных
            $this->initializeDatabase();

            try {
                switch ($command) {
                    case 'migrate:run':
                        return $this->runMigrations();

                    case 'migrate:create':
                        return $this->createMigration($args);

                    case 'migrate:reset':
                        return $this->resetMigrations();

                    case 'migrate:rollback':
                        return $this->rollbackMigrations($args);

                    case 'migrate:refresh':
                        return $this->refreshMigrations();

                    case 'db:seed':
                        return $this->runSeeder($args);

                    case 'make:controller':
                        return $this->makeController($args);

                    case 'make:model':
                        return $this->makeModel($args);

                    default:
                        echo "Unknown command: $command\n";
                        return $this->showHelp();
                }
            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                return 1;
            }
        }

        private function showHelp()
        {
            echo "Available commands:\n";
            foreach ($this->commands as $cmd => $description) {
                echo "  " . str_pad($cmd, 20) . " - " . $description . "\n";
            }
            return 0;
        }

        private function loadEnvironment()
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

        private function initializeDatabase()
        {
            // Просто инициализируем соединение
            Connection::getInstance();
        }

        private function listRoutes($args)
        {
            $app = new \App\Core\Application();
            $app->boot(); // Загружаем маршруты

            $router = $app->getContainer()->get('router');
            $routes = $router->getRoutes();

            echo "Registered routes:\n";
            foreach ($routes as $route) {
                echo str_pad($route['method'], 8) . " " . $route['path'] . "\n";
            }

            return 0;
        }

        private function runMigrations()
        {
            $migrator = new Migrator();
            $migrator->run();
            echo "Migrations completed successfully.\n";
            return 0;
        }

        private function createMigration($args)
        {
            if (!isset($args[2])) {
                echo "Usage: php console migrate:create <MigrationName> [--create=table_name|--table=table_name]\n";
                return 1;
            }

            $name = $args[2];
            $options = [];

            if (isset($args[3])) {
                parse_str(str_replace('--', '', $args[3]), $options);
            }

            $migrator = new Migrator();
            $migrator->create($name, $options);

            echo "Migration created successfully.\n";
            return 0;
        }

        private function resetMigrations()
        {
            $migrator = new Migrator();
            $migrator->reset();
            echo "Migrations reset successfully.\n";
            return 0;
        }

        private function rollbackMigrations($args)
        {
            $steps = isset($args[2]) ? (int)$args[2] : 1;
            $migrator = new Migrator();
            $migrator->rollback($steps);
            echo "Migrations rolled back successfully.\n";
            return 0;
        }

        private function refreshMigrations()
        {
            $migrator = new Migrator();
            $migrator->refresh();
            echo "Database refreshed successfully.\n";
            return 0;
        }

        private function runSeeder($args)
        {
            $seederClass = $args[2] ?? 'UserSeeder';
            $seederFile = SEEDS_PATH . $seederClass . '.php';

            if (!file_exists($seederFile)) {
                echo "Seeder file not found: $seederFile\n";
                return 1;
            }

            require_once $seederFile;

            if (!class_exists($seederClass)) {
                echo "Seeder class not found: $seederClass\n";
                return 1;
            }

            $seeder = new $seederClass();
            $seeder->run();

            echo "Seeder executed successfully.\n";
            return 0;
        }

        private function makeController($args)
        {
            if (!isset($args[2])) {
                echo "Usage: php console make:controller <ControllerName>\n";
                return 1;
            }

            $name = $args[2];
            $controllerPath = APP_PATH . 'Controllers/' . $name . '.php';

            if (file_exists($controllerPath)) {
                echo "Controller already exists: $controllerPath\n";
                return 1;
            }

            $namespace = 'App\Controllers';
            $template = "<?php
    
    namespace {$namespace};
    
    use App\Core\Controller;
    
    class {$name} extends Controller
    {
        // Add your controller methods here
    }
    ";

            if (file_put_contents($controllerPath, $template)) {
                echo "Controller created successfully: $controllerPath\n";
                return 0;
            } else {
                echo "Failed to create controller: $controllerPath\n";
                return 1;
            }
        }

        private function makeModel($args)
        {
            if (!isset($args[2])) {
                echo "Usage: php console make:model <ModelName>\n";
                return 1;
            }

            $name = $args[2];
            $modelPath = APP_PATH . 'Models/' . $name . '.php';

            if (file_exists($modelPath)) {
                echo "Model already exists: $modelPath\n";
                return 1;
            }

            $namespace = 'App\Models';
            $template = "<?php
    
    namespace {$namespace};
    
    use App\Core\Model;
    
    class {$name} extends Model
    {
        protected \$table = '" . strtolower($name) . "s';
        
        // Add your model methods here
    }
    ";

            if (file_put_contents($modelPath, $template)) {
                echo "Model created successfully: $modelPath\n";
                return 0;
            } else {
                echo "Failed to create model: $modelPath\n";
                return 1;
            }
        }
    }
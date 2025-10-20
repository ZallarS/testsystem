<?php

    namespace App\Core;

    class EnvironmentBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $this->loadEnvironmentVariables();
            $this->validateEnvironment();
        }

        private function loadEnvironmentVariables()
        {
            $envFile = BASE_PATH . '/.env';

            if (!file_exists($envFile)) {
                // Создаем минимальный .env файл если его нет
                $this->createDefaultEnvFile($envFile);
            }

            Env::load($envFile);
        }

        private function createDefaultEnvFile($envFile)
        {
            $defaultEnv = <<<ENV
    APP_ENV=development
    APP_SECRET=change-this-in-production-$(bin2hex(random_bytes(16)))
    APP_DEBUG=true
    
    DB_HOST=localhost
    DB_DATABASE=testsystem
    DB_USERNAME=root
    DB_PASSWORD=
    
    # Security
    SESSION_LIFETIME=3600
    CSRF_TOKEN_LIFETIME=3600
    
    # Cache
    CACHE_DRIVER=file
    CACHE_PATH=storage/cache
    
    ENV;

            file_put_contents($envFile, $defaultEnv);
            error_log("Created default .env file. Please configure it for production.");
        }

        private function validateEnvironment()
        {
            $requiredEnvVars = ['APP_SECRET', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
            $missing = [];

            foreach ($requiredEnvVars as $var) {
                if (empty($_ENV[$var])) {
                    $missing[] = $var;
                }
            }

            if (!empty($missing)) {
                // Более информативное сообщение об ошибке
                $message = "Missing required environment variables: " . implode(', ', $missing) . "\n";
                $message .= "Please check your .env file or create one from .env.example\n";
                $message .= "Current working directory: " . getcwd();

                throw new \RuntimeException($message);
            }

            // Validate APP_SECRET is not default in production
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                $defaultSecrets = [
                    'your-secret-key-change-this-in-production',
                    'change-this-in-production'
                ];

                if (in_array($_ENV['APP_SECRET'], $defaultSecrets)) {
                    throw new \RuntimeException(
                        'SECURITY ERROR: Please change APP_SECRET from default value in production!'
                    );
                }

                if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                    error_log('SECURITY WARNING: Debug mode enabled in production');
                    // В production автоматически отключаем debug
                    $_ENV['APP_DEBUG'] = 'false';
                }
            }
        }
    }
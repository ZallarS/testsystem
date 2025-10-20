<?php

    namespace App\Core;

    class EnvironmentBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $this->validateEnvironment();
            $this->loadEnvironmentVariables();
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
                throw new \RuntimeException('Missing required environment variables: ' . implode(', ', $missing));
            }

            // Production security checks
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                    error_log('SECURITY WARNING: Debug mode enabled in production');
                    $_ENV['APP_DEBUG'] = 'false';
                }
            }
        }

        private function loadEnvironmentVariables()
        {
            $envFile = BASE_PATH . '/.env';
            if (file_exists($envFile)) {
                Env::load($envFile);
            }
        }
    }
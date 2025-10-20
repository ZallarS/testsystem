<?php

    namespace App\Core;

    class ErrorHandlingBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $environment = $_ENV['APP_ENV'] ?? 'production';

            if ($environment === 'development') {
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
            } else {
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            }

            // Регистрируем обработчики ошибок
            ErrorHandler::register();
        }
    }